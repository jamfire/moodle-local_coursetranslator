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

/**
 * Translation Editor UI
 * @param {Object} config JS Config
 */
export const init = (config) => {
  /**
   * Convert a template string into HTML DOM nodes
   * @param  {String} string The template string
   * @return {Node}       The template HTML
   */
  const stringToHTML = (string) => {
    // See if DOMParser is supported
    var support = (() => {
      if (!window.DOMParser) {
        return false;
      }
      var parser = new DOMParser();
      try {
        parser.parseFromString("x", "text/html");
      } catch (err) {
        return false;
      }
      return true;
    })();

    // If DOMParser is supported, use it
    if (support) {
      var parser = new DOMParser();
      var doc = parser.parseFromString(string, "text/html");
      return doc.body.childNodes;
    }

    // Otherwise, fallback to old-school method
    var dom = document.createElement("div");
    dom.innerHTML = string;
    return dom;
  };

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
      }
      return "";
    };

    // Get searchex results.
    let result = text.replace(searchex, (match) => {
      let lang = config.lang;
      return replacecallback(lang, match);
    });

    // Return the found string.
    return result;
  };

  /**
   * Switch Translation Language
   */
  let localeSwitcher = document.querySelector(
    ".local-coursetranslator__localeswitcher"
  );
  localeSwitcher.addEventListener("change", (e) => {
    let url = new URL(window.location.href);
    let searchParams = url.searchParams;
    searchParams.set("course_lang", e.target.value);
    let newUrl = url.toString();

    window.location = newUrl;
  });

  /**
   * Show Updated Checkbox
   */
  let showUpdatedCheckbox = document.querySelector(
    ".local-coursetranslator__show-updated"
  );
  showUpdatedCheckbox.addEventListener("change", (e) => {
    let items = document.querySelectorAll('[data-status="updated"]');
    if (e.target.checked) {
      items.forEach((item) => {
        item.classList.remove("d-none");
      });
    } else {
      items.forEach((item) => {
        item.classList.add("d-none");
      });
    }
  });

  /**
   * Show Update Needed Checkbox
   */
  let showUpdateNeededCheckbox = document.querySelector(
    ".local-coursetranslator__show-needsupdate"
  );
  showUpdateNeededCheckbox.addEventListener("change", (e) => {
    let items = document.querySelectorAll('[data-status="needsupdate"]');
    if (e.target.checked) {
      items.forEach((item) => {
        item.classList.remove("d-none");
      });
    } else {
      items.forEach((item) => {
        item.classList.add("d-none");
      });
    }
  });

  /**
   * Select All Checkbox
   */
  const selectAll = document.querySelector(
    ".local-coursetranslator__select-all"
  );
  if (config.autotranslate) {
    selectAll.disabled = false;
  }
  selectAll.addEventListener("click", (e) => {
    // See if select all is checked
    let checked = e.target.checked;
    let checkboxes = document.querySelectorAll(
      ".local-coursetranslator__checkbox"
    );

    // Check/uncheck checkboxes
    if (checked) {
      checkboxes.forEach((e) => {
        e.checked = true;
      });
    } else {
      checkboxes.forEach((e) => {
        e.checked = false;
      });
    }
    toggleAutotranslateButton();
  });

  /**
   * Autotranslate Checkboxes
   */
  const checkboxes = document.querySelectorAll(
    ".local-coursetranslator__checkbox"
  );
  if (config.autotranslate) {
    checkboxes.forEach((e) => {
      e.disabled = false;
    });
  }
  checkboxes.forEach((e) => {
    e.addEventListener("change", () => {
      toggleAutotranslateButton();
    });
  });

  /**
   * Autotranslate Button Display
   * @returns void
   */
  const autotranslateButton = document.querySelector(
    ".local-coursetranslator__autotranslate-btn"
  );

  /**
   * Toggle Autotranslate Button
   */
  const toggleAutotranslateButton = () => {
    let checkboxItems = [];
    checkboxes.forEach((e) => {
      checkboxItems.push(e.checked);
    });
    let checked = checkboxItems.find((checked) => checked === true)
      ? true
      : false;
    if (config.autotranslate && checked) {
      autotranslateButton.disabled = false;
    } else {
      autotranslateButton.disabled = true;
    }
  };

  /**
   * Autotranslate Button Click
   * @returns void
   */
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
          window.console.log("deepl:", key, data);
          // Display translation
          editor.innerHTML = data.translations[0].text;
          // Save translation
          saveTranslation(key, editor, data.translations[0].text);
        } else {
          // Oh no! There has been an error with the request!
          window.console.log("error", status);
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
  const saveTranslation = (key, editor, text) => {
    // Get processing vars
    let element = editor.closest(".local-coursetranslator__editor");
    let id = element.getAttribute("data-id");
    let tid = element.getAttribute("data-tid");
    let table = element.getAttribute("data-table");
    let field = element.getAttribute("data-field");

    // Get the latest field data
    let fielddata = {};
    fielddata.courseid = config.courseid;
    fielddata.id = parseInt(id);
    fielddata.table = table;
    fielddata.field = field;

    // Get the latest data to parse text against.
    ajax.call([
      {
        methodname: "local_coursetranslator_get_field",
        args: {
          data: [fielddata],
        },
        done: (data) => {
          // The latests field text so multiple translators can work at the same time
          let fieldtext = data[0].text;

          // Field text exists
          if (data.length > 0) {
            // Updated hidden textarea with updatedtext
            let textarea = document.querySelector(
              '.local-coursetranslator__textarea[data-key="' + key + '"]'
            );
            // Get the updated text
            let updatedtext = getupdatedtext(fieldtext, text);

            // Build the data object
            let data = {};
            data.courseid = config.courseid;
            data.id = parseInt(id);
            data.tid = tid;
            data.table = table;
            data.field = field;
            data.text = updatedtext;

            // Success Message
            const successMessage = () => {
              editor.classList.add("local-coursetranslator__success");
              // Add saved indicator
              let indicator =
                '<div class="local-coursetranslator__success-message" data-key="' +
                key +
                '">' +
                config.autosavedmsg +
                "</div>";
              editor.after(...stringToHTML(indicator));

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
              window.console.log(error);
              editor.classList.add("local-coursetranslator__error");
            };

            // Submit the request
            ajax.call([
              {
                methodname: "local_coursetranslator_update_translation",
                args: {
                  data: [data],
                },
                done: (data) => {
                  // Print response to console log
                  if (config.debug > 0) {
                    window.console.log("ws: ", key, data);
                  }

                  // Display success message
                  if (data.length > 0) {
                    successMessage();
                    textarea.innerHTML = data[0].text;

                    // Update source lang if necessary
                    if (config.currentlang === config.lang) {
                      document.querySelector(
                        '[data-sourcetext-key="' + key + '"]'
                      ).innerHTML = text;
                    }
                  } else {
                    // Something went wrong with the data
                    errorMessage();
                  }
                },
                fail: (error) => {
                  // An error occurred
                  errorMessage(error);
                },
              },
            ]);
          } else {
            // Something went wrong with field retrieval
            window.console.log(data);
          }
        },
        fail: (error) => {
          // An error occurred
          window.console.log(error);
        },
      },
    ]);
  };

  /**
   * Update Textarea
   * @param {string} fieldtext Latest text from database
   * @param {string} text Text to update
   * @returns {string}
   */
  const getupdatedtext = (fieldtext, text) => {
    let lang = config.lang;

    // Search for {mlang} not found.
    let mlangtext = '{mlang ' + lang + '}' + text + '{mlang}';

    // Return new mlang text if mlang has not been used before
    if (fieldtext.indexOf("{mlang") === -1) {
      if (lang === "other") {
        return mlangtext;
      } else {
        return (
          "{mlang other}" +
          fieldtext +
          "{mlang}{mlang " +
          lang +
          "}" +
          text +
          "{mlang}"
        );
      }
    }

    // Use regex to replace the string
    let pattern = `{*mlang +(${lang})}(.*?){*mlang*}`;
    let replacex = new RegExp(pattern, "dgis");
    let matches = fieldtext.match(replacex);

    // Return the updated string
    if (!matches) {
      return fieldtext + "{mlang " + lang + "}" + text + "{mlang}";
    } else {
      return fieldtext.replace(
        replacex,
        "{mlang " + lang + "}" + text + "{mlang}"
      );
    }
  };

  /**
   * Get the Translation using Moodle Web Service
   * @returns void
   */
  window.addEventListener("load", function () {
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

          saveTranslation(key, editor, text);
        });

        // Remove status classes
        editor.addEventListener("click", () => {
          editor.classList.remove("local-coursetranslator__success");
          editor.classList.remove("local-coursetranslator__error");
        });
      });
  });

  /**
   * Get text from processing areas and add them to contenteditables
   */
  window.addEventListener("load", () => {
    let textareas = document.querySelectorAll(
      ".local-coursetranslator__textarea"
    );
    textareas.forEach((textarea) => {
      // Get relevent keys and text
      let key = textarea.getAttribute("data-key");
      let text = textarea.innerHTML;
      let editor = document.querySelector(
        '[data-key="' + key + '"] [contenteditable="true"]'
      );

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
        document
          .querySelector(
            '.local-coursetranslator__textarea[data-key="' + key + '"]'
          )
          .remove();
        let p = document.createElement("p");
        p.innerHTML = "<em><small>" + config.multiplemlang + "</small></em>";
        this.document
          .querySelector(
            '.local-coursetranslator__editor[data-key="' + key + '"]'
          )
          .append(p);
      } else {
        editor.innerHTML = parsedtext;
      }
    });
  });
};
