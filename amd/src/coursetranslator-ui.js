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

/**
 * Translation Editor UI
 * @param {Object} config JS Config
 */
export const init = (config) => {

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

};