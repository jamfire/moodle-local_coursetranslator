<?php
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

/**
 * Local Course Translator Strings
 *
 * @package    local_coursetranslator
 * @copyright  2022 Kaleb Heitzman <kaleb@jamfire.io>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @see        https://docs.moodle.org/dev/String_API
 */

defined('MOODLE_INTERNAL') || die();

// General strings.
$string['pluginname'] = 'Course Translator';
$string['coursetranslator:edittranslations'] = 'Edit Translations';
$string['edittranslation'] = 'Edit Translation';

// DeepL strings.
$string['apikey'] = 'API Key for DeepL Translate';
$string['apikey_desc'] = 'Copy your api key from DeepL to use machine translation.';
$string['usedeepl'] = 'Use DeepL';
$string['usedeepl_desc'] = 'Check this checkbox if you want the plugin to use the DeepL translate api, otherwise auto generated translations are same as original.';
$string['deeplpro'] = 'Use DeepL Pro?';
$string['deeplpro_desc'] = 'Enable this to use DeepL Pro instead of the free version of DeepL.';
$string['useautotranslate'] = 'Enable autotranslate for translation page';
$string['useautotranslate_desc'] = 'Enable autotranslate on the translation page. This gives translators the abilty to autotranslate content without enabling autotranslate on individual page loads.';
$string['supported_languages'] = 'bg,cs,da,de,el,en,es,et,fi,fr,hu,it,ja,lt,lv,nl,pl,pt,ro,ru,sk,sl,sv,zh'; // Do not change between translations.

// Template strings.
$string['t_contextDeepl'] = 'Course context ';
$string['t_deeplapidoc'] = 'see detail on deepl\'s documentation';
$string['t_contextDeeplPlaceholder'] = 'Tell the translator (Deepl) about the context, to help it translate in a more contextual way... ';
$string['t_sourceLang'] = 'Source lang ';
$string['t_select_target_language'] = 'Target language {mlang XX}';
$string['t_word_count'] = '{$a} words';
$string['t_char_count'] = '{$a} characters';
$string['t_word_count_sentence'] = 'Total {$a->wordcount} words, {$a->charcount} characters ({$a->charcountspaces} chars including spaces)';

$string['t_char_count_spaces'] = '({$a} char including spaces)';
$string['t_autotranslate'] = 'Translate';
$string['t_source_text'] = 'Source lang :';
$string['t_translation'] = 'Target lang: {$a}';
$string['t_autosaved'] = 'Autosaved';
$string['t_selectall'] = 'Select All';
$string['t_status'] = 'Status';
$string['t_other'] = 'Other (other)';
$string['t_multiplemlang'] = 'This field is using advanced {mlang} usage. Please edit translation using standard Moodle editor or simplify to a single mlang tag per language.';
$string['t_needsupdate'] = 'Update Needed';
$string['t_uptodate'] = 'Updated';
$string['t_edit'] = 'Edit in place';
$string['t_viewsource'] = 'View Source';
$string['t_seeSetting'] = 'See Deepl\'s settings';
$string['t_splitsentences'] = 'Split sentences?';
$string['t_splitsentences_0'] = 'no splitting at all';
$string['t_splitsentences_1'] = 'splits on punctuation and on newlines (default)';
$string['t_splitsentences_nonewlines'] = 'splits on punctuation only, ignoring newlines';
$string['t_preserveformatting'] = 'Preserve formatting';
$string['t_formality'] = 'Formality';
$string['t_formality_default'] = 'default';
$string['t_formality_less'] = 'less';
$string['t_formality_more'] = 'more';
$string['t_formality_prefer_more'] = 'prefer more';
$string['t_formality_prefer_less'] = 'prefer less';
$string['t_glossaryid'] = 'Glossary id';
$string['t_glossaryid_placeholder'] = 'Glossary id should you have one...';
$string['t_taghandling'] = 'Handle tags as : ';
$string['t_outlinedetection'] = 'Outline detection';
$string['t_tagsplaceholder'] = 'List all tags (separate tag with comma ",")';
$string['t_nonsplittingtags'] = 'Non splitting tags';
$string['t_splittingtags'] = 'Splitting tags';
$string['t_ignoretags'] = 'Tags to ignore';
