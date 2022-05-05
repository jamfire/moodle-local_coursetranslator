// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/*
 * @module     local_coursetranslator/coursetranslator
 * @copyright  2022 Kaleb Heitzman <kaleb@jamfire.io>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// import libs
import ajax from "core/ajax";
import notification from "core/notification";

/**
 * Translation Editor UI
 * @param {Object} config JS Config
 */
export const init = (config) => {

  /**
   * {mlang} searchex regex
   */
  const searchex =
    /{\s*mlang\s+((?:[a-z0-9_-]+)(?:\s*,\s*[a-z0-9_-]+\s*)*)\s*}(.*?){\s*mlang\s*}/dgis;

  /**
   * Search for mlang tags
   *
   * The code for this js parser was adapted from filter/multilang2
   *
   * @param {string} text Text with {mlang}
   * @returns {string}
   */
  const mlangparser = (text) => {
    // Search for {mlang} not found.
    if (text.match(searchex) === null) {
      return text;
    }

    // Replace callback for searchex results.
    const replacecallback = (lang, match) => {
      let blocklang = match.split(searchex)[1];
      let blocktext = match.split(searchex)[2];
      if (blocklang === lang) {
        return blocktext;
      } else {
        return "";
      }
    };

    // Get searchex results.
    let result = text.replace(searchex, (match) => {
      let lang = config.lang;
      return replacecallback(lang, match);
    });

    // No results were found, return text in mlang other
    // @todo: rewrite this to use searchex instead for more consistency
    if (result.length === 0) {
      let mlangpattern = "{mlang other}(.*?){mlang}";
      let mlangex = new RegExp(mlangpattern, "dgis");
      let matches = text.match(mlangex);
      if (matches && matches[0].split(searchex)[2]) {
        return matches[0].split(searchex)[2];
      }
    }

    // Return the found string.
    return result;
  };

  /**
   * Autotranslate Button Click
   * @returns void
   */
  const autotranslateButton = document.querySelector(
    ".local-coursetranslator__autotranslate-btn"
  );
  autotranslateButton.addEventListener("click", () => {
    document
      .querySelectorAll(".local-coursetranslator__checkbox:checked")
      .forEach((e) => {
        let key = e.getAttribute("data-key");
        getTranslation(key);
      });
  });

  /**
   * Send for Translation to DeepL
   * @param {Integer} key Translation Key
   */
  const getTranslation = (key) => {
    // Get the editor
    let editor = document.querySelector(
      '.local-coursetranslator__editor[data-key="' +
        key +
        '"] [contenteditable="true"]'
    );

    // Get the source text
    let sourceText = document.querySelector(
      '[data-sourcetext-key="' + key + '"]'
    ).innerHTML;

    // Build formData
    let formData = new FormData();
    formData.append("text", sourceText);
    formData.append("source_lang", "en");
    formData.append("target_lang", config.lang);
    formData.append("preserve_formatting", 1);
    formData.append("auth_key", config.apikey);
    formData.append("tag_handling", "xml");
    formData.append("split_sentences", "nonewlines");

    // Update the translation
    let xhr = new XMLHttpRequest();
    xhr.onreadystatechange = () => {
      if (xhr.readyState === XMLHttpRequest.DONE) {
        var status = xhr.status;
        if (status === 0 || (status >= 200 && status < 400)) {
          // The request has been completed successfully
          let data = JSON.parse(xhr.responseText);
          // Display translation
          editor.innerHTML = data.translations[0].text;
          // Save translation
          savetranslation(key, editor, data.translations[0].text);
        } else {
          // Oh no! There has been an error with the request!
          notification.alert(config.error, status, config.continue);
        }
      }
    };
    xhr.open("POST", config.deeplurl);
    xhr.send(formData);
  };

  /**
   * Save Translation to Moodle
   * @param  {String} key Data Key
   * @param  {Node} editor HTML Editor Node
   * @param  {String} text Updated Text
   */
  const savetranslation = (key, editor, text) => {
    let params = keyparser(key);
    let format = parseInt(editor
      .closest('.local-coursetranslator__editor')
      .getAttribute('data-format'));

    // Get the latest field data
    let fielddata = Object.assign({}, {
      courseid: config.courseid,
      id: params.id,
      table: params.table,
      field: params.field,
    });

    // Get the latest data to parse text against.
    ajax.call([
      {
        methodname: "local_coursetranslator_get_field",
        args: {
          data: [fielddata],
        },
        done: (data) => {
          // Field text exists
          if (data.length > 0) {
            // The latests field text so multiple translators can work at the same time
            let fieldtext = data[0].text;

            // Updated hidden textarea with updatedtext
            let textarea = document.querySelector(
              '.local-coursetranslator__textarea[data-key="' + key + '"]'
            );
            // Get the updated text
            let updatedtext = getupdatedtext(fieldtext, text);

            // Build the data object
            let tdata = Object.assign(params, {
              courseid: config.courseid,
              text: updatedtext
            });

            // Success Message
            const successMessage = () => {
              editor.classList.add("local-coursetranslator__success");
              // Add saved indicator
              let indicator = document.createElement('div');
              indicator.classList.add('local-coursetranslator__success-message');
              indicator.setAttribute('data-key', key);
              indicator.innerHTML = config.autosavedmsg;
              editor.after(indicator);

              let status = document.querySelector(
                '[data-status-key="' + key + '"'
              );
              status.classList.replace("badge-danger", "badge-success");
              status.innerHTML = config.uptodate;

              // Remove success message after a few seconds
              setTimeout(() => {
                let indicatorNode = document.querySelector(
                  '.local-coursetranslator__success-message[data-key="' +
                    key +
                    '"]'
                );
                editor.parentNode.removeChild(indicatorNode);
              }, 3000);
            };

            // Error Mesage
            const errorMessage = (error) => {
              notification.alert(config.error, error, config.continue);
              editor.classList.add("local-coursetranslator__error");
            };

            // Text too long
            const textLengthError = () => {
              let p = document.createElement('p');
              p.classList.add('local-coursetranslator__textlengtherror');
              p.setAttribute('data-key', key);
              p.innerHTML = '<small><em>' + config.textlengtherror + '</em></small>';
              editor.after(p);

              editor.classList.add("local-coursetranslator__error");
            };

            // Text is to long for varchar 255 field
            if (format === 0 && updatedtext.length > 255) {
              textLengthError();
              return;
            }

            // Submit the request
            ajax.call([
              {
                methodname: "local_coursetranslator_update_translation",
                args: {
                  data: [tdata],
                },
                done: (data) => {
                  // Display success message
                  if (data.length > 0) {
                    successMessage();
                    textarea.value = data[0].text;
                    // Update source lang if necessary
                    if (
                      config.currentlang === config.lang
                      || config.lang === 'other'
                    ) {
                      document.querySelector(
                        '[data-sourcetext-key="' + key + '"]'
                      ).innerHTML = text;
                    }
                  } else {
                    // Something went wrong with the data
                    errorMessage(data);
                  }
                },
                fail: (error) => {
                  // An error occurred
                  errorMessage(error.message);
                },
              },
            ]);
          }
        },
        fail: (error) => {
          // An error occurred
          notification.alert(config.error, error.error, config.continue);
        },
      },
    ]);
  };

  /**
   * Key Parser to return params
   * @param {string} key Key string
   * @returns {object}
   */
  const keyparser = (key) => {
    let keys = key.split('-');
    let params = {};
    params.table = keys[0];
    params.field = keys[1];
    params.id = parseInt(keys[2]);
    params.tid = parseInt(keys[3]);
    return params;
  };

  /**
   * Update Textarea
   * @param {string} fieldtext Latest text from database
   * @param {string} text Text to update
   * @returns {string}
   */
  const getupdatedtext = (fieldtext, text) => {
    // Get current lang
    let lang = config.lang;

    // Search for {mlang} not found.
    let mlangtext = '{mlang ' + lang + '}' + text + '{mlang}';

    /**
     * {mlang} not found
     * Create new mlang text if mlang has not been used before
     */
    if (fieldtext.indexOf("{mlang") === -1) {
      if (lang === "other") {
        return mlangtext;
      } else {
        return "{mlang other}" + fieldtext + "{mlang}{mlang " + lang + "}" + text + "{mlang}";
      }
    }

    // Check if mlang exists on text
    let mlangexists = false;
    let splits = fieldtext.match(searchex);
    splits.forEach(split => {
      let blocklang = split.split(searchex)[1];
      if (blocklang === config.lang) {
        mlangexists = true;
      }
    });

    // Return updated text based on existing mlang
    if (mlangexists) {
      // Replace callback for searchex results.
      const replacecallback = (match) => {
        let blocklang = match.split(searchex)[1];
        if (blocklang === config.lang) {
          return '{mlang ' + config.lang + '}' + text + '{mlang}';
        } else {
          return match;
        }
      };

      // Get searchex results.
      let result = fieldtext.replace(searchex, (match) => {
        return replacecallback(match);
      });

      return result;
    } else {
      // Append new mlang text to field text
      return fieldtext + '{mlang ' + config.lang + '}' + text + '{mlang}';
    }

  };

  /**
   * Get the Translation using Moodle Web Service
   * @returns void
   */
  window.addEventListener("load", () => {
    document
      .querySelectorAll(
        '.local-coursetranslator__editor [contenteditable="true"]'
      )
      .forEach((editor) => {
        // Save translation
        editor.addEventListener("focusout", () => {
          // Get Processing Information
          let text = editor.innerHTML;
          let element = editor.closest(".local-coursetranslator__editor");
          let key = element.getAttribute("data-key");

          savetranslation(key, editor, text);
        });

        // Remove status classes
        editor.addEventListener("click", () => {
          let element = editor.closest(".local-coursetranslator__editor");
          let key = element.getAttribute("data-key");
          editor.classList.remove("local-coursetranslator__success");
          editor.classList.remove("local-coursetranslator__error");
          let textlengtherror = document.querySelector('.local-coursetranslator__textlengtherror[data-key="' + key + '"]');
          if (textlengtherror) {
            textlengtherror.remove();
          }
        });
      });
  });

  /**
   * Get text from processing areas and add them to contenteditables.
   * Listen for focusout event and save field.
   * @returns void
   */
  window.addEventListener("load", () => {

    // Get all textareas
    let textareas = document.querySelectorAll(
      ".local-coursetranslator__textarea"
    );

    // Populate editors based on textarea text
    textareas.forEach((textarea) => {

      // Get relevent keys and text
      let key = textarea.getAttribute("data-key");
      let text = textarea.value;
      let editor = document.querySelector(
        '[data-key="' + key + '"] [contenteditable="true"]'
      );

      // Get mlang matches
      let langpattern = "{mlang " + config.lang + "}(.*?){mlang}";
      let langex = new RegExp(langpattern, "dgis");
      let matches = text.match(langex);

      // Parse the text for mlang
      let parsedtext = mlangparser(text);

      if (matches && matches.length === 1) {
        // Updated contenteditables with parsedtext
        editor.innerHTML = parsedtext;
      } else if (matches && matches.length > 1) {
        document
          .querySelector('input[type="checkbox"][data-key="' + key + '"]')
          .remove();
        document
          .querySelector(
            '.local-coursetranslator__editor[data-key="' + key + '"] > *'
          )
          .remove();
        let p = document.createElement("p");
        p.innerHTML = "<em><small>" + config.multiplemlang + "</small></em>";
        document
          .querySelector(
            '.local-coursetranslator__editor[data-key="' + key + '"]'
          )
          .append(p);
      } else {
        editor.innerHTML = parsedtext;
      }
    });

    // Listen for textarea changes and update db/editors
    textareas.forEach(textarea => {
      textarea.addEventListener('focusout', () => {

        // Get relevant keys and text
        let key = textarea.getAttribute("data-key");
        let text = textarea.value;
        let editor = document.querySelector(
          '[data-key="' + key + '"] [contenteditable="true"]'
        );

        // Parse the text for mlang
        let parsedtext = mlangparser(text);
        savetranslation(key, editor, parsedtext);

      });
    });
  });
};
