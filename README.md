# Course Translator for Moodle

![Moodle CI](https://github.com/jamfire/moodle-local_coursetranslator/actions/workflows/moodle-ci.yml/badge.svg) ![Dependency Review](https://github.com/jamfire/moodle-local_coursetranslator/actions/workflows/dependency-review.yml/badge.svg)

Course Translator is a local moodle plugin that provides a content translation page for courses and automatic machine translation using the DeepL Pro Translation api. Currently it can translate content two levels deep and is developed for those who want to translate a course all on one page without having to navigate to each module and update translations. [Multi-Language Content (v2)](https://moodle.org/plugins/filter_multilang2) is a dependency of this plugin and will not work without it.

## Installation

Clone or download this plugin to ```/moodlewww/local/coursetranslator``` and run through the database upgrade process.

## Configuration

Course Translator will extend Moodle with the ```local/coursetranslator:edittranslations``` capability. Assign the capability to a new Translator role or add it to one of your existing roles. It will also add a ```local_coursetranslator_update_translation``` web service for the translation page to perform ajax requests against.

To configure the plugin, navigate to **Site Administration -> Plugins -> Local plugins -> Manage local plugins.** From this page you can configure DeepL settings, specify wether you are using DeepL API Free or Deepl API Pro, and enable/disable the autotranslate feature on the translation page. Visit the [DeepL API page](https://www.deepl.com/pro-api) to signup for an api key that you can enter into local plugin settings.

<img src="https://ik.imagekit.io/1zvlk0e7l/moodle/settings_v2spLzFgi.png?ik-sdk-version=javascript-1.4.3&updatedAt=1650925753470" alt="Course Translator Settings" />

## Translating Content

To begin translating content, visit a course, open the course settings action menu, and then go to **Course Translator**.

<img src="https://ik.imagekit.io/1zvlk0e7l/moodle/action-menu_zsYkTOVeN.png?ik-sdk-version=javascript-1.4.3&updatedAt=1650925893813" alt="Course Settings Action Menu" />

You will be sent to the translation page for the course and the course language will automatically be set to **other**. This will automatically insert ```{mlang other}Your Content{mlang}``` tags when first translating your content. Please checkout the <a href="https://moodle.org/plugins/filter_multilang2">mlang docs</a> to understand more.

To change the language you want to translate in, choose a language from the **Select {mlang} language** dropdown. Note: Changing the site wide language will not change the course language you are translating in. You need to use the locale switcher just above the translation table. You can verify the language you are translating to by looking at the head of the translation column. Translations are automatically saved when you click outside of the form input or tab to the next field. The translation page uses a custom implementation of the Atto editor so any changes you make for your toolbar, etc in Site Administration will not apply.

To automatically translate a field, check the row's checkbox and then click autotranslate. You can also use the select all checkbox next to status in the translation table header. This will generate a translation from the DeepL API and then automatically save it to your course. If you need to see all of the content and ```{mlang}``` tags for the activty you are editing, you will need to go back to your course and edit the activity from there. The Course Translator filters out all unneeded content to give translators and easy and quick way to translate Moodle courses on the fly.

_At this time, Course Translator does not have the ability to translate advanced usage of mlang in content. For example, this includes the use of multiple mlang tags spread throughout content that utilize the same language._

<img src="https://ik.imagekit.io/1zvlk0e7l/moodle/course-translator-v0.9.2_prvB1rCSS.png?ik-sdk-version=javascript-1.4.3&updatedAt=1651374909723" alt="Course Translator Page">

## Compatability

This plugin has been tested on Moodle 3.11 and Moodle 4.0.

## How does this plugin differe from Content Translation Manager and Content Translation Filter?

This plugin does not translate every string on your site. It is only meant for translating courses and it uses Moodle's built in multilingual features along with ```{mlang}``` to translate your content. When you backup and restore courses, your translations will migrate with your content. Updating your source content will provide a "Update Needed" status message on the course translation page.

## Submit an issue

Please [submit issues here.](https://github.com/jamfire/moodle-local_coursetranslator/issues)

## Changelog

See the [CHANGES.md](CHANGES.md) documentation.

## Contributing

See the [CONTRIBUTING.md](CONTRIBUTING.md) documentation.