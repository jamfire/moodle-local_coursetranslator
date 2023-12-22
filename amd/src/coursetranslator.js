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
import Selectors from "./selectors";
import Modal from 'core/modal';
// Initialize the temporary translations dictionary @todo make external class
let tempTranslations = {};
let editorType = '';
let config = {};
let autotranslateButton = {};
let checkboxes = [];

const registerEventListeners = ()=>{
  document.addEventListener('change', e=>{
    if (e.target.closest(Selectors.actions.targetSwitcher)) {
      switchTarget(e);
    }
    if (e.target.closest(Selectors.actions.sourceSwitcher)) {
      switchSource(e);
    }
    if (e.target.closest(Selectors.actions.showUpdated)) {
      // showUpdated(e);
      showRows(Selectors.statuses.updated, e.target.checked);
    }
    if (e.target.closest(Selectors.actions.showNeedUpdate)) {
      //neededUpdate(e);
      showRows(Selectors.statuses.needsupdate, e.target.checked);
    }
    if (e.target.closest(Selectors.actions.checkBoxes)) {
        onItemChecked(e);
    }
  });
  document.addEventListener('click', e=>{
    if(e.target.closest(Selectors.actions.toggleMultilang)){
      //window.console.info(e.target.id);
      onToggleMultilang(e.target.closest(Selectors.actions.toggleMultilang));
    }
    if (e.target.closest(Selectors.actions.autoTranslateBtn)) {
      if (config.currentlang == config.lang || config.lang == undefined) {
        Modal.create({
          title: 'Cannot call deepl',
          body: `<p>Both languges are the same {$config.lang}</p>`,
          show: true,
          removeOnClose: true,
        });
      } else {
        doAutotranslate(e);
      }
    }
    if (e.target.closest(Selectors.actions.selecAllBtn)) {
      toggleAllCheckboxes(e);
    }
  });
};
const registerUI = ()=>{
  autotranslateButton = document.querySelector(Selectors.actions.autoTranslateBtn);
  checkboxes = document.querySelectorAll(Selectors.actions.checkBoxes);
  window.console.log(Selectors.statuses.checkedCheckBoxes);
};

/**
 * Translation Editor UI
 * @param {Object} cfg JS Config
 */
export const init = (cfg) => {
  config = cfg;
  window.console.log(config);
  editorType = config.userPrefs;

  registerUI();
  registerEventListeners();
  /**
   * Convert a template string into HTML DOM nodes
   * @param  {String} string The template string
   * @return {Node}       The template HTML
   *
  const stringToHTML = (string) => {
    // See if DOMParser is supported
    let parser;
    const support = (() => {
      if (!window.DOMParser) {
        return false;
      }
      parser = new DOMParser();
      try {
        parser.parseFromString("x", "text/html");
      } catch (err) {
        return false;
      }
      return true;
    })();
    // If DOMParser is supported, use it
    if (support) {
      parser = new DOMParser();
      const doc = parser.parseFromString(string, "text/html");
      return doc.body.childNodes;
    }
    // Otherwise, fallback to old-school method
    const dom = document.createElement("div");
    dom.innerHTML = string;
    return dom;
  };
   */

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
    if (result.length === 0) {
      let mlangpattern = "{mlang other}(.*?){mlang}";
      let mlangex = new RegExp(mlangpattern, "dgis");
      let matches = text.match(mlangex);
      if (matches[0].split(searchex)[2]) {
        return matches[0].split(searchex)[2];
      }
    }

    // Return the found string.
    return result;
  };
  const selectAllBtn = document.querySelector(Selectors.actions.selecAllBtn);
  if (config.autotranslate) {
    selectAllBtn.disabled = false;
  }

  /**
   * Validaate translation ck
   */
  // const validators = document.querySelectorAll("[data-key-validator]");
  const validators = document.querySelectorAll(Selectors.actions.validatorsBtns);
  validators.forEach((e)=>{
    // Get the stored data and do the saving from editors content
    e.addEventListener('click', (e)=> {
      let key = e.target.parentElement.dataset.keyValidator;
      // Window.console.log(key, "save");
      if (tempTranslations[key] === null || tempTranslations[key] === undefined) {
        /**
         * @todo do a UI feedback (disable save )
         */
        window.console.log(`Transaltion key "${key}" is undefined `);
      } else {
        saveTranslation(
            key,
            tempTranslations[key].editor,
            tempTranslations[key].editor.innerHTML
        );
      }

    });
  });

  /**
   * Autotranslate Checkboxes
   */
  /* const checkboxes = document.querySelectorAll(
    ".local-coursetranslator__checkbox"
  );*/
  // window.console.log(config, config.autotranslate, checkboxes);
  if (config.autotranslate) {
    checkboxes.forEach((e) => {
      // Window.console.log(e);
      e.disabled = false;
    });
  }
  checkboxes.forEach((e) => {
    e.addEventListener("change", () => {
      toggleAutotranslateButton();
    });
  });


  /**
   * Save Translation to Moodle
   * @param  {String} key Data Key
   * @param  {Node} editor HTML Editor Node
   * @param  {String} text Updated Text
   * @todo 3rd param is to refactor remove as it is the editors content
   */
  const saveTranslation = (key, editor, text) => {
    window.console.log(key);
    // Get processing vars
    // let element = editor.closest(Selectors.editors.all);
    let icon = document.querySelector(replaceKey(Selectors.actions.validatorIcon, key));
    let selector = Selectors.editors.multiples.editorsWithKey.replace("<KEY>", key);
    window.console.log(selector);
    window.console.log(document.querySelector(selector));
    let element = document.querySelector(selector);
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
          window.console.log(data);
          // The latests field text so multiple translators can work at the same time
          let fieldtext = data[0].text;

          // Field text exists
          if (data.length > 0) {
            // Updated hidden textarea with updatedtext
            let textarea = document.querySelector(
                Selectors.editors.multiples.textAreas
                    .replace("<KEY>", key));
            // Get the updated text
            let updatedtext = getupdatedtext(fieldtext, text);

            // Build the data object
            let tdata = {};
            tdata.courseid = config.courseid;
            tdata.id = parseInt(id);
            tdata.tid = tid;
            tdata.table = table;
            tdata.field = field;
            tdata.text = updatedtext;
            // Success Message
            const successMessage = () => {
              /** @todo cleanup comments*/
              // editor.classList.add("local-coursetranslator__success");
              element.classList.add("local-coursetranslator__success");
              /* // Add saved indicator
              let indicator =
                `<div
                   <!--class="local-coursetranslator__success-message"-->
                   data-status="local-coursetranslator/success-message"
                   data-key="${key}"
                 >${config.autosavedmsg}</div>`;
              element.after(...stringToHTML(indicator));
              */
              icon.setAttribute('data-status', "local-coursetranslator/success");
              /* Let status = document.querySelector(
                  Selectors.statuses.keys
                      .replace("<KEY>", key));
              status.classList.replace("badge-danger", "badge-success");
              status.innerHTML = config.uptodate;*/

              // Remove success message after a few seconds
              /* setTimeout(() => {
                let indicatorNode = document.querySelector(
                    Selectors.statuses.successMessages
                        .replace("<KEY>", key));
                element.parentNode.removeChild(indicatorNode);
              }, 3000);*/
              setTimeout(()=>{
 icon.setAttribute('data-status', "local-coursetranslator/saved");
});
            };

            // Error Mesage
            const errorMessage = (error) => {
              window.console.log(error);
              editor.classList.add("local-coursetranslator__error");
              icon.setAttribute('data-status', "local-coursetranslator/failed");
              if (error) {
                textarea.innerHTML = error;
              }
            };

            // Submit the request
            ajax.call([
              {
                methodname: "local_coursetranslator_update_translation",
                args: {
                  data: [tdata],
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
                      document.querySelector(Selectors.sourcetexts.keys.replace('<KEY>', key))
                          .innerHTML = text;
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
    let mlangtext = `{mlang ${lang}}${text}{mlang}`;

    // Return new mlang text if mlang has not been used before
    if (fieldtext.indexOf("{mlang") === -1) {
      if (lang === "other") {
        return mlangtext;
      } else {
        return (
          `{mlang other} ${fieldtext} {mlang}{mlang ${lang}} ${text} {mlang}`
        );
      }
    }

    // Use regex to replace the string
    let pattern = `{*mlang +(${lang})}(.*?){*mlang*}`;
    let replacex = new RegExp(pattern, "dgis");
    let matches = fieldtext.match(replacex);

    // Return the updated string
    const updatedString = `{mlang ${lang}} ${text} {mlang}`;
    if (matches) {
      return fieldtext.replace(replacex, updatedString);
    } else {
      return fieldtext + updatedString;
    }
  };

  /**
   * Get the Translation using Moodle Web Service
   * @returns void
   */
  window.addEventListener("load", () => {
    // Document.querySelectorAll('.local-coursetranslator__editor [contenteditable="true"]')
    document.querySelectorAll(Selectors.editors.contentEditable)
      .forEach((editor) => {
        // Save translation
        editor.addEventListener("focusout", () => {
          return;
          // Get Processing Information
          let text = editor.innerHTML;
          // Let element = editor.closest(".local-coursetranslator__editor");
          let element = editor.closest(Selectors.editors.all);
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
    let textareas = document.querySelectorAll(Selectors.editors.textarea);
    textareas.forEach((textarea) => {
      // Get relevent keys and text
      let key = textarea.getAttribute("data-key");
      let text = textarea.innerHTML;
      /**
       * @todo review selector
       */
      let editor = document.querySelector(
          Selectors.editors.multiples.contentEditableKeys
              .replace("<KEY>", key));

      let langpattern = `{mlang ${config.lang}}(.*?){mlang}`;
      let langex = new RegExp(langpattern, "dgis");
      let matches = text.match(langex);

      // Parse the text for mlang
      let parsedtext = mlangparser(text);

      if (matches && matches.length === 1) {
        // Updated contenteditables with parsedtext
        editor.innerHTML = parsedtext;
      } else if (matches && matches.length > 1) {
        // Const dataKey = `data-key="${key}"`;
        document.querySelector(Selectors.editors.multiples.checkBoxesWithKey
            .replace('<KEY>', key)).remove();
        document.querySelector(Selectors.editors.multiples.editorChilds
            .replace('<KEY>', key)).remove();
        document.querySelector(Selectors.editors.multiples.textAreas
            .replace('<KEY>', key)).remove();
        let p = document.createElement("p");
        p.innerHTML = `<em><small>${config.multiplemlang}</small></em>`;
        document.querySelector(Selectors.editors.multiples.editorsWithKey
            .replace('<KEY>', key)).append(p);
      } else {
        editor.innerHTML = parsedtext;
      }
    });
  });
};
const onItemChecked = (e) => {
  // If(e.target.checked)
  //window.console.info(e.target.attributes['data-key'].value);
  toggleStatus(e.target.getAttribute('data-key'), e.target.checked);
  /*
  let k = e.target.attributes['data-key'].value;
  let statusItem = document.querySelector(replaceKey(Selectors.actions.validatorIcon, k));
  window.console.info(statusItem);
  if (e.target.checked) {
    statusItem.setAttribute('data-status', "local-coursetranslator/totranslate");
  } else {
    statusItem.setAttribute('data-status', "local-coursetranslator/wait");
  }
  */

};
const toggleStatus=(key, checked)=>{
  let s = 'wait';
  //let statusItem = document.querySelector(replaceKey(Selectors.actions.validatorIcon, key));
  if (checked) {
    s = "totranslate";
  }
  document.querySelector(replaceKey(Selectors.actions.validatorIcon, key))
      .setAttribute('data-status',`local-coursetranslator/${s}`);
};
/**
 * Eventlistener for show update checkbox
 * @param {Event} e
 */
/*
const showUpdated = (e) =>{
  let items = document.querySelectorAll(Selectors.statuses.updated);
  items.forEach((item) => {
    toggleRowVisibility(item, e.target.checked);
    toggleStatus(item.getAttribute('data-row-id'), e.target.checked);
  });
  // if (e.target.checked) {
  //   items.forEach((item) => {
  //     item.classList.remove("d-none");
  //   });
  // } else {
  //   items.forEach((item) => {
  //     item.classList.add("d-none");
  //   });
  // }
};
*/

/**
 * Event listener to check if update are needed
 * @param {Event} e
 */
/*const neededUpdate = (e)=> {
    // Window.console.info("Need update toggled");
    // window.console.info("source_lang", config.currentlang);
    // window.console.info("target_lang", config.lang);

  let items = document.querySelectorAll(Selectors.statuses.needsupdate);
  items.forEach((item) => {
    toggleRowVisibility(item, e.target.checked);
    toggleStatus(item.getAttribute('data-row-id'), e.target.checked);
  });
  // if (e.target.checked) {
  //   items.forEach((item) => {
  //     item.classList.remove("d-none");
  //   });
  // } else {
  //   items.forEach((item) => {
  //     item.classList.add("d-none");
  //   });
  // }
};*/
const showRows=(selector, selected)=>{
  window.console.log(selector, selected);
  let items = document.querySelectorAll(selector);
  items.forEach((item) => {
    let k = item.getAttribute('data-row-id');
    toggleRowVisibility(item, selected);
    // when a row is toggled then we don't want it to be selected and sent from translation.
    item.querySelector(replaceKey(Selectors.editors.multiples.checkBoxesWithKey,k)).checked = false;
    toggleStatus(k, false);
  });
};
const toggleRowVisibility=(row, checked)=>{
  if(checked){
    row.classList.remove("d-none");
  }else{
    row.classList.add("d-none");
  }
};
/**
 * Event listener to switch target lang
 * @param {Event} e
 */
const switchTarget = (e) => {
  window.console.info('switchTarget');
  let url = new URL(window.location.href);
  let searchParams = url.searchParams;
  searchParams.set("target_lang", e.target.value);
  let newUrl = url.toString();
  window.location = newUrl;
};
/**
 * Event listener to switch source lang
 * Hence reload the page and change the site main lang
 * @param {Event} e
 */
const switchSource = (e) => {
  window.console.info('switchSource');
  let url = new URL(window.location.href);
  let searchParams = url.searchParams;
  searchParams.set("lang", e.target.value);
  let newUrl = url.toString();
  window.location = newUrl;
};

/**
 * Launch autotranslation
 */
const doAutotranslate = () => {
  document
      // .querySelectorAll(".local-coursetranslator__checkbox:checked")
      .querySelectorAll(Selectors.statuses.checkedCheckBoxes)
      .forEach((ckBox) => {
        let key = ckBox.getAttribute("data-key");
        getTranslation(key);
      });
};
/**
 * @todo extract images ALT tags to send for translation
 * Send for Translation to DeepL
 * @param {Integer} key Translation Key
 */
const getTranslation = (key) => {
  // Store the key in the dictionary
  tempTranslations[key] = {};
  // Get the editor
  let editor = findEditor(key);
  // Get the source text
  let sourceText = document.querySelector(Selectors.sourcetexts.keys.replace("<KEY>", key)).getAttribute("data-sourcetext-raw");
/*  Let sourceText = document.querySelector(Selectors.sourcetexts.keys.replace("<KEY>",key))
      .innerHTML;*/
  // window.console.log(sourceText);
  let icon = document.querySelector(replaceKey(Selectors.actions.validatorIcon, key));
  // Initialize global dictionary with this key's editor

  tempTranslations[key] = {
    'editor': editor,
    'source': sourceText,
    'translation': ''
  };
  window.console.log(tempTranslations);
  // Build formData
  let formData = new FormData();
  formData.append("text", sourceText);
  // FormData.append("source_lang", "en");
  formData.append("source_lang", config.currentlang.toUpperCase());
  formData.append("target_lang", config.lang.toUpperCase());
  formData.append("auth_key", config.apikey);
  formData.append("tag_handling", document.querySelector(Selectors.deepl.tag_handling).checked ? 'html' : 'xml');//
  formData.append("context", document.querySelector(Selectors.deepl.context).value ?? null); //
  formData.append("split_sentences", document.querySelector(Selectors.deepl.split_sentences).value);//
  formData.append("preserve_formatting", document.querySelector(Selectors.deepl.preserve_formatting).checked);//
  formData.append("formality", document.querySelector('[name="local-coursetranslator/formality"]:checked').value);
  formData.append("glossary_id", document.querySelector(Selectors.deepl.glossary_id).value);//
  formData.append("outline_detection", document.querySelector(Selectors.deepl.outline_detection).checked);//
  formData.append("non_splitting_tags", toJsonArray(document.querySelector(Selectors.deepl.non_splitting_tags).value));
  formData.append("splitting_tags", toJsonArray(document.querySelector(Selectors.deepl.splitting_tags).value));
  formData.append("ignore_tags", toJsonArray(document.querySelector(Selectors.deepl.ignore_tags).value));
  // Window.console.log(config.currentlang);
   window.console.log("Send deepl:", formData);
  // Update the translation
  let xhr = new XMLHttpRequest();
  xhr.onreadystatechange = () => {
    if (xhr.readyState === XMLHttpRequest.DONE) {
      const status = xhr.status;
      if (status === 0 || (status >= 200 && status < 400)) {
        // The request has been completed successfully
        let data = JSON.parse(xhr.responseText);
        window.console.log("deepl:", key, data);
        // Window.console.log(config.currentlang);
        // window.console.log(editor);
        // Display translation
        editor.innerHTML = data.translations[0].text;
        // Save translation
        // saveTranslation(key, editor, data.translations[0].text);
        // store the translation in the global object
        tempTranslations[key].translation = data.translations[0].text;
        icon.setAttribute('data-status', 'local-coursetranslator/tosave');
      } else {
        // Oh no! There has been an error with the request!
        window.console.log("error", status);
        // Let icon=  document.querySelector(replaceKey(Selectors.actions.validatorIcon,key));
        icon.setAttribute('data-status', 'local-coursetranslator/failed');
      }
    }
  };
   xhr.open("POST", config.deeplurl);
   xhr.send(formData);
};
/**
 * @todo URGENT something got broken with finding editor ...
 * Get the editor container based on recieved current user's
 * editor preference.
 * @param {Integer} key Translation Key
 */
const findEditor = (key) => {
  // Let q = '';
  // window.console.log("document.querySelector('" + q + "')");
  // window.console.log("editors pref : " + editorType);
  let e = document.querySelector(Selectors.editors.types.basic
      .replace("<KEY>", key));
  if (e === null) {
    switch (editorType) {
      case "atto" :
        e = document.querySelector(
            Selectors.editors.types.atto
                .replace("<KEY>", key)); break;
      case "tiny":
        e = document.querySelector(Selectors.editors.types.tiny
            .replace("<KEY>", key))
            .contentWindow.tinymce; break;
      case 'marklar':
      case "textarea" :
        e = document.querySelector(Selectors.editors.types.other
            .replace("<KEY>", key)); break;
    }
  }
  return e;
};
/**
 *
 * @param {Event} e Event
 */
const toggleAllCheckboxes = (e)=>{
  // See if select all is checked
  let checked = e.target.checked;

  // Check/uncheck checkboxes
  if (checked) {
    checkboxes.forEach((i) => {
      // toggle check box upon visibility
      i.checked = !getParentRow(i).classList.contains('d-none');
      toggleStatus(i.getAttribute('data-key'), i.checked);
    });
  } else {
    checkboxes.forEach((i) => {
      i.checked = false;
      toggleStatus(i.getAttribute('data-key'), false);
    });
  }
  toggleAutotranslateButton();
};
const getParentRow = (node) =>{
  return node.closest(replaceKey(Selectors.sourcetexts.parentrow, node.getAttribute('data-key')));
};
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
 *
 * @param {Event} e Event
 */
const onToggleMultilang = (e) =>{
  window.console.log(e);
  e.classList.toggle("showing");
  let keyid = e.getAttribute('aria-controls');
  let key = keyidToKey(keyid);
  window.console.log(e, key, keyid);
  let source = document.querySelector(replaceKey(Selectors.sourcetexts.keys, key));
  let multilang = document.querySelector(replaceKey(Selectors.sourcetexts.multilangs, keyid));
  source.classList.toggle("show");
  multilang.classList.toggle("show");
};
/**
 *
 * @param {string} s
 * @param {string} sep
 * @returns {string}
 */
const toJsonArray = (s, sep = ",") => {
  return JSON.stringify(s.split(sep));
};
const replaceKey = (s, k)=>{
  return s.replace("<KEY>", k);
};
/*const regFrom = /^(.+)\[(.+)\]\[(.+)\]$/i;*/
const regTo = /^(.+)-(.+)-(.+)$/i;
/*
const keyToKeyid=(k)=>{

  let m = regFrom.match(k);
  return `${m[1]}-${m[1]}-${m[1]}`;
};

 */
const keyidToKey=(k)=>{
  let m = k.match(regTo);
  return `${m[1]}[${m[2]}][${m[3]}]`;
};
