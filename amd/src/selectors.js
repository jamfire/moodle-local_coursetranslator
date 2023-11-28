export default {
    actions :{
        validatorsBtns:'[data-key-validator]',
        checkBoxes:'[data-action="local-coursetranslator/checkbox"]',
        selecAllBtn:'[data-action="local-coursetranslator/select-all"]',
        autoTranslateBtn:'[data-action="local-coursetranslator/autotranslate-btn"]',
        localeSwitcher:'[data-action="local-coursetranslator/localeswitcher"]',
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
            atto:'[data-action="local-coursetranslator/editor"][data-key="<KEY>"] [contenteditable="true"]',
            other:'[data-action="local-coursetranslator/editor"][data-key="<KEY>"] textarea[name="<KEY>[text]"]',
            tiny:'[data-action="local-coursetranslator/editor"][data-key="<KEY>"] iframe'
        }
    },
    sourcetexts:{
        keys: '[data-sourcetext-key="<KEY>"]'
    }
};
