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
 * @copyright  2024 Bruno Baudry <bruno.baudry@bfh.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// Import libs
import ajax from "core/ajax";
import Selectors from "./selectors";
import Modal from 'core/modal';
// Initialize the temporary translations dictionary @todo make external class
let tempTranslations = {};
let mainEditorType = '';
let config = {};
let autotranslateButton = {};
let checkboxes = [];
let sourceLang = "";
let targetLang = "";

const registerEventListeners = () => {

    document.addEventListener('change', e => {
        if (e.target.closest(Selectors.actions.targetSwitcher)) {
            switchTarget(e);
        }
        if (e.target.closest(Selectors.actions.sourceSwitcher)) {
            switchSource(e);
        }
        if (e.target.closest(Selectors.actions.showUpdated)) {
            showRows(Selectors.statuses.updated, e.target.checked);
        }
        if (e.target.closest(Selectors.actions.showNeedUpdate)) {
            showRows(Selectors.statuses.needsupdate, e.target.checked);
        }
        if (e.target.closest(Selectors.actions.checkBoxes)) {
            onItemChecked(e);
        }
    });
    document.addEventListener('click', e => {
        if (e.target.closest(Selectors.actions.toggleMultilang)) {
            onToggleMultilang(e.target.closest(Selectors.actions.toggleMultilang));
        }
        if (e.target.closest(Selectors.actions.autoTranslateBtn)) {
            if (config.currentlang === config.lang || config.lang === undefined) {
                Modal.create({
                    title: 'Cannot call deepl',
                    body: `<p>Both languges are the same {$config.lang}</p>`,
                    show: true,
                    removeOnClose: true,
                });
            } else {
                doAutotranslate();
            }
        }
        if (e.target.closest(Selectors.actions.selectAllBtn)) {
            toggleAllCheckboxes(e);
        }
    });

};
const registerUI = () => {
    try {
        autotranslateButton = document.querySelector(Selectors.actions.autoTranslateBtn);
        checkboxes = document.querySelectorAll(Selectors.actions.checkBoxes);
        sourceLang = document.querySelector(Selectors.actions.sourceSwitcher).value;
        targetLang = document.querySelector(Selectors.actions.targetSwitcher).value;
    } catch (e) {
        if (config.debug) {
            window.console.error(e.message);
        }
    }
};

/**
 * Translation Editor UI
 * @param {Object} cfg JS Config
 */
export const init = (cfg) => {
    config = cfg;
    if (config.debug > 0) {
        window.console.info("debugging coursetranslator");
        window.console.info(config);
    }
    mainEditorType = config.userPrefs;
    // Setup
    registerUI();
    registerEventListeners();
    toggleAutotranslateButton();
    const selectAllBtn = document.querySelector(Selectors.actions.selectAllBtn);
    selectAllBtn.disabled = sourceLang === targetLang;
    /**
     * Validaate translation ck
     */
    const validators = document.querySelectorAll(Selectors.actions.validatorsBtns);
    validators.forEach((e) => {
        // Get the stored data and do the saving from editors content
        e.addEventListener('click', (e) => {
            let key = e.target.parentElement.dataset.keyValidator;
            if (tempTranslations[key] === null || tempTranslations[key] === undefined) {
                /**
                 * @todo do a UI feedback (disable save )
                 */
                window.console.log(`Transaltion key "${key}" is undefined `);
            } else {
                saveTranslation(key);
            }

        });
    });

    /**
     * Autotranslate Checkboxes
     */
    if (config.autotranslate) {
        checkboxes.forEach((e) => {
            e.disabled = sourceLang === targetLang;
        });
    }
    checkboxes.forEach((e) => {
        e.addEventListener("click", () => {
            toggleAutotranslateButton();
        });
    });


    /**
     * Save Translation to Moodle
     * @param  {String} key Data Key
     */
    const saveTranslation = (key) => {
        // Get processing vars
        let editor = tempTranslations[key].editor;
        let text = editor.innerHTML; // We keep the editors text in case translation is edited
        let sourceText = tempTranslations[key].source;
        let icon = document.querySelector(replaceKey(Selectors.actions.validatorIcon, key));
        let selector = Selectors.editors.multiples.editorsWithKey.replace("<KEY>", key);
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
        if (config.debug > 0) {
            window.console.log(fielddata);
        }
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
                            Selectors.editors.multiples.textAreas
                                .replace("<KEY>", key));
                        // Get the updated text
                        let updatedtext = getupdatedtext(fieldtext, text, sourceText);

                        // Build the data object
                        let tdata = {};
                        tdata.courseid = config.courseid;
                        tdata.id = parseInt(id);
                        tdata.tid = tid;
                        tdata.table = table;
                        tdata.field = field;
                        tdata.text = updatedtext;
                        if (config.debug > 0) {
                            window.console.log(tdata);
                        }
                        // Success Message
                        const successMessage = () => {
                            element.classList.add("local-coursetranslator__success");
                            // Add saved indicator
                            icon.setAttribute('role', 'status');
                            icon.setAttribute('data-status', "local-coursetranslator/success");
                            // Remove success message after a few seconds
                            setTimeout(() => {
                                icon.setAttribute('data-status', "local-coursetranslator/saved");
                            });
                        };

                        // Error Mesage
                        const errorMessage = (error) => {
                            editor.classList.add("local-coursetranslator__error");
                            icon.setAttribute('role', 'status');
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
     * @param {string} source Original text translated from
     * @returns {string}
     */
    const getupdatedtext = (fieldtext, text, source) => {
        let targetlang = targetLang;
        // Search for {mlang} not found.
        let startOther = `{mlang other}`;
        let otherlangtext = `${startOther}${source}{mlang}`;
        let targetLangTag = `{mlang ${targetlang}}`;
        let targetlangtext = `${targetLangTag}${text}{mlang}`;
        if (config.debug > 0) {
            window.console.log(targetlang);
            window.console.log(startOther);
            window.console.log(otherlangtext);
            window.console.log(targetLangTag);
            window.console.log(targetlangtext);
        }
        // Return new mlang text if mlang has not been used before.
        if (fieldtext.indexOf("{mlang") === -1) {
            return otherlangtext + targetlangtext;
        }
        // Use regex to replace the string
        let alllanpattern = `({mlang [a-z]{2,5}})(.*?){mlang}`;
        let alllangregex = new RegExp(alllanpattern, "g");
        let all = {};
        let tagReg = new RegExp("{mlang (other|[a-z]{2})}", "");
        let splited = fieldtext.split(alllangregex);
        let foundsourcetag = "";
        var l = "";
        for (var i in splited) {
            if (splited[i] === "") {
                continue;
            }
            if (splited[i].match(tagReg)) {
                l = splited[i].match(tagReg)[0];
            } else if (l !== "") {
                all[l] = splited[i];
                if (splited[i] === source) {
                    foundsourcetag = l;
                }
                l = "";
            }
        }
        if (foundsourcetag !== startOther) {
            // We need to replace the source.
            delete all[foundsourcetag];
        }
        // If there is a other tag we replace it by the source.
        // @todo a mechanism to propose to the user to select another tag for this.
        all[startOther] = source;
        all[targetLangTag] = text;
        let s = "";
        for (let tag in all) {
            s += tag + all[tag] + "{mlang}";
        }
        return s;
    };
};

const onItemChecked = (e) => {
    toggleStatus(e.target.getAttribute('data-key'), e.target.checked);
};
const toggleStatus = (key, checked) => {
    let s = 'wait';
    if (checked) {
        s = "totranslate";
    }
    document.querySelector(replaceKey(Selectors.actions.validatorIcon, key))
        .setAttribute('data-status', `local-coursetranslator/${s}`);
};


/**
 * Shows/hides rows
 * @param {string} selector
 * @param {boolean} selected
 */
const showRows = (selector, selected) => {
    let items = document.querySelectorAll(selector);
    items.forEach((item) => {
        let k = item.getAttribute('data-row-id');
        toggleRowVisibility(item, selected);
        // When a row is toggled then we don't want it to be selected and sent from translation.
        item.querySelector(replaceKey(Selectors.editors.multiples.checkBoxesWithKey, k)).checked = false;
        toggleStatus(k, false);
    });
    toggleAutotranslateButton();
};
const toggleRowVisibility = (row, checked) => {
    if (checked) {
        row.classList.remove("d-none");
    } else {
        row.classList.add("d-none");
    }
};
/**
 * Event listener to switch target lang
 * @param {Event} e
 */
const switchTarget = (e) => {
    let url = new URL(window.location.href);
    let searchParams = url.searchParams;
    searchParams.set("target_lang", e.target.value);
    window.location = url.toString();
};
/**
 * Event listener to switch source lang
 * Hence reload the page and change the site main lang
 * @param {Event} e
 */
const switchSource = (e) => {
    let url = new URL(window.location.href);
    let searchParams = url.searchParams;
    searchParams.set("lang", e.target.value);
    window.location = url.toString();
};
/**
 * Launch autotranslation
 */
const doAutotranslate = () => {
    document
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
    let editorSettings = findEditor(key);
    if (config.debug > 0) {
        window.console.log(editorSettings);
    }
    let editor = editorSettings.editor;
    let editorType = editorSettings.editorType;

    // Get the source text
    let sourceText = document.querySelector(Selectors.sourcetexts.keys.replace("<KEY>", key)).getAttribute("data-sourcetext-raw");
    let icon = document.querySelector(replaceKey(Selectors.actions.validatorIcon, key));
    // Initialize global dictionary with this key's editor
    tempTranslations[key] = {
        'editorType': editorType,
        'editor': editor,
        'source': sourceText,
        'translation': ''
    };
    // Build formData
    let formData = new FormData();
    formData.append("text", sourceText);
    formData.append("source_lang", sourceLang.toUpperCase());
    formData.append("target_lang", targetLang.toUpperCase());
    formData.append("auth_key", config.apikey);
    formData.append("tag_handling", document.querySelector(Selectors.deepl.tagHandling).checked ? 'html' : 'xml');//
    formData.append("context", document.querySelector(Selectors.deepl.context).value ?? null); //
    formData.append("split_sentences", document.querySelector(Selectors.deepl.splitSentences).value);//
    formData.append("preserve_formatting", document.querySelector(Selectors.deepl.preserveFormatting).checked);//
    formData.append("formality", document.querySelector('[name="local-coursetranslator/formality"]:checked').value);
    formData.append("glossary_id", document.querySelector(Selectors.deepl.glossaryId).value);//
    formData.append("outline_detection", document.querySelector(Selectors.deepl.outlineDetection).checked);//
    formData.append("non_splitting_tags", toJsonArray(document.querySelector(Selectors.deepl.nonSplittingTags).value));
    formData.append("splitting_tags", toJsonArray(document.querySelector(Selectors.deepl.splittingTags).value));
    formData.append("ignore_tags", toJsonArray(document.querySelector(Selectors.deepl.ignoreTags).value));
    if (config.debug) {
        window.console.log("Send deepl:", formData);
    }
    // Update the translation
    let xhr = new XMLHttpRequest();
    xhr.onreadystatechange = () => {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            const status = xhr.status;
            if (status === 0 || (status >= 200 && status < 400)) {
                // The request has been completed successfully
                let data = JSON.parse(xhr.responseText);
                // Display translation
                editor.innerHTML = data.translations[0].text;
                // Store the translation in the global object
                tempTranslations[key].translation = data.translations[0].text;
                icon.setAttribute('role', 'button');
                icon.setAttribute('data-status', 'local-coursetranslator/tosave');
                injectImageCss(editorSettings);
            } else {
                // Oh no! There has been an error with the request!
                icon.setAttribute('data-status', 'local-coursetranslator/failed');
                icon.setAttribute('role', 'status');
            }
        }
    };
    xhr.open("POST", config.deeplurl);
    xhr.send(formData);
};
/**
 * Inject css to highlight ALT text of image not loaded because of @@POLUGINFILE@@
 * @param {Integer} editorSettings
 * */
const injectImageCss = (editorSettings) => {
    // Prepare css to inject in iframe editors
    const css = document.createElement('style');
    css.textContent = 'img{background-color:yellow !important;font-style: italic;}';
    if (editorSettings.editorType === "iframe") {
        let editorschildrens = Array.from(editorSettings.editor.parentElement.children);
        let found = false;
        for (let j in editorschildrens) {
            let e = editorschildrens[j];
            if (e.innerText === css.innerText) {
                found = true;
                break;
            }
        }
        if (!found) {
            editorSettings.editor.parentElement.appendChild(css);
        }
    }
};
/**
 * Get the editor container based on recieved current user's
 * editor preference.
 * @param {Integer} key Translation Key
 */
const findEditor = (key) => {
    let e = document.querySelector(Selectors.editors.types.basic
        .replace("<KEY>", key));
    let et = 'basic';
    if (e === null) {
        switch (mainEditorType) {
            case "atto" :
                et = 'iframe';
                e = document.querySelector(
                    Selectors.editors.types.atto
                        .replaceAll("<KEY>", key));
                break;
            case "tiny":
                et = 'iframe';
                e = document.querySelector(Selectors.editors.types.tiny
                    .replaceAll("<KEY>", key))
                    .contentWindow.tinymce;
                break;
            case 'marklar':
            case "textarea" :
                e = document.querySelector(Selectors.editors.types.other
                    .replaceAll("<KEY>", key));
                break;
        }
    }
    return {editor: e, editorType: et};
};
/**
 * Toggle checkboxes
 * @param {Event} e Event
 */
const toggleAllCheckboxes = (e) => {
    // Check/uncheck checkboxes
    if (e.target.checked) {
        checkboxes.forEach((i) => {
            // Toggle check box upon visibility
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
const getParentRow = (node) => {
    return node.closest(replaceKey(Selectors.sourcetexts.parentrow, node.getAttribute('data-key')));
};
/**
 * Toggle Autotranslate Button
 */
const toggleAutotranslateButton = () => {
    autotranslateButton.disabled = true;
    for (let i in checkboxes) {
        let e = checkboxes[i];
        if (e.checked) {
            autotranslateButton.disabled = false;
            break;
        }
    }
};
/**
 * Multilang button handler
 * @param {Event} e Event
 */
const onToggleMultilang = (e) => {
    e.classList.toggle("showing");
    let keyid = e.getAttribute('aria-controls');
    let key = keyidToKey(keyid);
    let source = document.querySelector(replaceKey(Selectors.sourcetexts.keys, key));
    let multilang = document.querySelector(replaceKey(Selectors.sourcetexts.multilangs, keyid));
    source.classList.toggle("show");
    multilang.classList.toggle("show");
};
/**
 * Json helper
 * @param {string} s
 * @param {string} sep
 * @returns {string}
 */
const toJsonArray = (s, sep = ",") => {
    return JSON.stringify(s.split(sep));
};
/**
 * Simple helper to manage selectors
 * @param {string} s
 * @param {string} k
 * @returns {*}
 */
const replaceKey = (s, k) => {
    return s.replace("<KEY>", k);
};
/**
 * Transforms a keyid to a key
 * @param {string} k
 * @returns {`${*}[${*}][${*}]`}
 */
const keyidToKey = (k) => {
    let m = k.match(/^(.+)-(.+)-(.+)$/i);
    return `${m[1]}[${m[2]}][${m[3]}]`;
};
/**
 * {mlang} searchex regex
 */
/*
const searchex =
  /{\s*mlang\s+((?:[a-z0-9_-]+)(?:\s*,\s*[a-z0-9_-]+\s*)*)\s*}(.*?){\s*mlang\s*}/dgis;
*/
/**
 * Search for mlang tags
 *
 * The code for this js parser was adapted from filter/multilang2
 * @todo store for future ref
 * @param {string} text Text with {mlang}
 * @returns {string}
 */
/*
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
*/
