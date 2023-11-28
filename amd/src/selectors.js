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
        needsupdate : '[data-status="needsupdate"]'
    },
    editors:{
        textarea:'[data-action="local-coursetranslator/textarea"',
        all:'[data-action="local-coursetranslator/editors"]',
        contentEditable: '[data-action="local-coursetranslator/editors"] [contenteditable="true"]'
    }
};
