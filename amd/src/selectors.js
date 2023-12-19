export default {
    actions :{
        validatorsBtns:'[data-key-validator]',
        checkBoxes:'[data-action="local-coursetranslator/checkbox"]',
        selecAllBtn:'[data-action="local-coursetranslator/select-all"]',
        autoTranslateBtn:'[data-action="local-coursetranslator/autotranslate-btn"]',
        targetSwitcher:'[data-action="local-coursetranslator/target-switcher"]',
        sourceSwitcher:'[data-action="local-coursetranslator/source-switcher"]',
        showNeedUpdate:'[data-action="local-coursetranslator/show-needsupdate"]',
        showUpdated:'[data-action="local-coursetranslator/show-updated"]'
    },
    statuses : {
        checkedCheckBoxes : '[data-action="local-coursetranslator/checkbox"]:checked',
        updated : '[data-status="updated"]',
        needsupdate : '[data-status="needsupdate"]',
        keys:'[data-status-key="<KEY>"',
        successMessages:'[data-status="local-coursetranslator/success-message"][data-key="<KEY>"]'
    },
    editors:{
        textarea:'[data-action="local-coursetranslator/textarea"',
        all:'[data-action="local-coursetranslator/editor"]',
        contentEditable: '[data-action="local-coursetranslator/editor"] [contenteditable="true"]',
        multiples:{
            checkBoxesWithKey:'input[type="checkbox"][data-key="<KEY>"]',
            editorChilds:'[data-action="local-coursetranslator/editor"][data-key="<KEY>"] > *',
            textAreas:'[data-action="local-coursetranslator/textarea"][data-key="<KEY>"]',
            editorsWithKey:'[data-action="local-coursetranslator/editor"][data-key="<KEY>"]',
            contentEditableKeys:'[data-key="<KEY>"] [contenteditable="true"]'
        },
        types:{
            basic: '[data-action="local-coursetranslator/editor"][data-key="<KEY>"] [contenteditable="true"]',
            atto:'[data-action="local-coursetranslator/editor"][data-key="<KEY>"] [contenteditable="true"]',
            other:'[data-action="local-coursetranslator/editor"][data-key="<KEY>"] textarea[name="<KEY>[text]"]',
            tiny:'[data-action="local-coursetranslator/editor"][data-key="<KEY>"] iframe'
        }
    },
    sourcetexts:{
        /*keys: '[data-sourcetext-key="<KEY>"]',*/
        keys: '[data-sourcetext-key="<KEY>"]'
    },
    deepl:{
        context: '[data-id="local-coursetranslator/context"]',
        non_splitting_tags: '[data-id="local-coursetranslator/non_splitting_tags"]',
        splitting_tags: '[data-id="local-coursetranslator/splitting_tags"]',
        ignore_tags: '[data-id="local-coursetranslator/ignore_tags"]',
        preserve_formatting: '[data-id="local-coursetranslator/preserve_formatting"]',
        formality: '[data-id="local-coursetranslator/formality"]',
        glossary_id: '[data-id="local-coursetranslator/glossary_id"]',
        tag_handling: '[data-id="local-coursetranslator/tag_handling"]',
        outline_detection: '[data-id="local-coursetranslator/outline_detection"]',
        split_sentences: 'input[name="local-coursetranslator/split_sentences"]'
    }
};
