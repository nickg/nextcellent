/******/ (function() { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./block-editor/api.js":
/*!*****************************!*\
  !*** ./block-editor/api.js ***!
  \*****************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "fetchAlbums": function() { return /* binding */ fetchAlbums; },
/* harmony export */   "fetchGallerys": function() { return /* binding */ fetchGallerys; },
/* harmony export */   "fetchImages": function() { return /* binding */ fetchImages; }
/* harmony export */ });
const fetchGallerys = async searchTerm => {
  const res = await fetch(nggData.siteUrl + `/index.php?term=${searchTerm}&method=autocomplete&type=gallery&format=json&callback=json&limit=50`);
  return await res.json();
};
const fetchAlbums = async searchTerm => {
  const res = await fetch(nggData.siteUrl + `/index.php?term=${searchTerm}&method=autocomplete&type=album&format=json&callback=json&limit=50`);
  return await res.json();
};
const fetchImages = async searchTerm => {
  const res = await fetch(nggData.siteUrl + `/index.php?term=${searchTerm}&method=autocomplete&type=image&format=json&callback=json&limit=50`);
  return await res.json();
};


/***/ }),

/***/ "./block-editor/blocks/recent-images-block/edit.js":
/*!*********************************************************!*\
  !*** ./block-editor/blocks/recent-images-block/edit.js ***!
  \*********************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ Edit; }
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_server_side_render__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/server-side-render */ "@wordpress/server-side-render");
/* harmony import */ var _wordpress_server_side_render__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_server_side_render__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _gerneral_components_number_of_images_input_NumberOfImages__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../gerneral-components/number-of-images-input/NumberOfImages */ "./block-editor/gerneral-components/number-of-images-input/NumberOfImages.js");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var _editor_scss__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./editor.scss */ "./block-editor/blocks/recent-images-block/editor.scss");
/* harmony import */ var _gerneral_components_autocomplete_Autocomplete__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ../../gerneral-components/autocomplete/Autocomplete */ "./block-editor/gerneral-components/autocomplete/Autocomplete.js");
/* harmony import */ var _api__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ../../api */ "./block-editor/api.js");
/* harmony import */ var _gerneral_components_template_radio_group_Template__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! ../../gerneral-components/template-radio-group/Template */ "./block-editor/gerneral-components/template-radio-group/Template.js");
/* harmony import */ var _gerneral_components_mode_select_ModeSelect__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! ../../gerneral-components/mode-select/ModeSelect */ "./block-editor/gerneral-components/mode-select/ModeSelect.js");



//import Autocomplete  from '../../gerneral-components/autocomplete/Autocomplete'





/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/
 */


/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */






/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/block-edit-save/#edit
 * @return {WPElement} Element to render.
 */
function Edit(_ref) {
  let {
    attributes,
    setAttributes
  } = _ref;
  const [gallery, setGallery] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(attributes !== null && attributes !== void 0 && attributes.galleryLabel ? attributes.galleryLabel : "");
  const [number, setNumber] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(attributes !== null && attributes !== void 0 && attributes.numberOfImages ? attributes.numberOfImages : "0");
  const [galleryTemplate, setGalleryTemplate] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(attributes !== null && attributes !== void 0 && attributes.galleryTemplate ? attributes.galleryTemplate : "gallery");
  const [mode, setMode] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(attributes !== null && attributes !== void 0 && attributes.mode ? attributes.mode : "");
  const handleAutocompleteSelect = value => {
    if ((value === null || value === void 0 ? void 0 : value.label) !== gallery) {
      setGallery(value === null || value === void 0 ? void 0 : value.label);
    }
  };
  const handleNumberOfImagesChange = value => {
    if (value !== number) {
      setNumber(value);
    }
  };
  const handleModeChange = value => {
    if (value !== mode) {
      setMode(value);
    }
  };
  const handleGalleryTemplateSelection = value => {
    setGalleryTemplate(value);
  };
  const attributeSetter = e => {
    e.stopPropagation();
    e.preventDefault();
    let newAttributes = {};
    if (gallery) {
      newAttributes["galleryLabel"] = gallery;
    }
    if (number) {
      newAttributes["numberOfImages"] = number;
    }
    if (mode) {
      newAttributes["mode"] = mode;
    }
    if (galleryTemplate) {
      newAttributes["galleryTemplate"] = galleryTemplate;
    }
    setAttributes(newAttributes);
  };
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", (0,_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_4__.useBlockProps)(), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_4__.InspectorControls, {
    key: "setting",
    id: "nextcellent-recent-images-block-controlls"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.PanelBody, {
    title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_5__.__)("Basics", "nggallery")
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("fieldset", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_gerneral_components_number_of_images_input_NumberOfImages__WEBPACK_IMPORTED_MODULE_2__["default"], {
    value: number,
    onChange: handleNumberOfImagesChange
  }))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.PanelBody, {
    title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_5__.__)("Type options", "nggallery")
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("fieldset", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_gerneral_components_mode_select_ModeSelect__WEBPACK_IMPORTED_MODULE_10__["default"], {
    type: "recent",
    value: mode,
    onChange: handleModeChange
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_5__.__)("In what order the images are shown. Upload order uses the ID's, date taken uses the EXIF data and user defined is the sort mode from the settings.", "nggallery")), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_gerneral_components_autocomplete_Autocomplete__WEBPACK_IMPORTED_MODULE_7__["default"], {
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_5__.__)("Select a gallery:", "nggallery"),
    preSelected: gallery,
    onSelect: handleAutocompleteSelect,
    fetch: _api__WEBPACK_IMPORTED_MODULE_8__.fetchGallerys
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_5__.__)("If a gallery is selected, only images from that gallery will be shown.", "nggallery")), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_gerneral_components_template_radio_group_Template__WEBPACK_IMPORTED_MODULE_9__["default"], {
    type: "recent",
    value: galleryTemplate,
    onChecked: handleGalleryTemplateSelection
  }))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    id: "nextcellent-block-set-button",
    className: "components-button editor-post-publish-button editor-post-publish-button__button is-primary",
    onClick: attributeSetter,
    disabled: number <= 0
  }, "Set")), attributes.numberOfImages > 0 && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)((_wordpress_server_side_render__WEBPACK_IMPORTED_MODULE_1___default()), {
    className: "nextcellent-recent-images-block-render",
    block: "nggallery/recent-images-block",
    attributes: attributes
  }), !attributes.numberOfImages && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_5__.__)("You need to select a number of images.", "nggallery")));
}

/***/ }),

/***/ "./block-editor/blocks/recent-images-block/index.js":
/*!**********************************************************!*\
  !*** ./block-editor/blocks/recent-images-block/index.js ***!
  \**********************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/blocks */ "@wordpress/blocks");
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_blocks__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _style_scss__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./style.scss */ "./block-editor/blocks/recent-images-block/style.scss");
/* harmony import */ var _block_json__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./block.json */ "./block-editor/blocks/recent-images-block/block.json");
/* harmony import */ var _edit__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./edit */ "./block-editor/blocks/recent-images-block/edit.js");
/* harmony import */ var _save__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./save */ "./block-editor/blocks/recent-images-block/save.js");

/**
 * Registers a new block provided a unique name and an object defining its behavior.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */


/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * All files containing `style` keyword are bundled together. The code used
 * gets applied both to the front of your site and to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */


/**
 * Internal dependencies
 */



const {
  name,
  ...settings
} = _block_json__WEBPACK_IMPORTED_MODULE_3__;

/**
 * Every block starts by registering a new block type definition.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
(0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_1__.registerBlockType)(name, {
  ...settings,
  icon: (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("svg", {
    viewBox: "0 0 24 24",
    xmlns: "http://www.w3.org/2000/svg",
    width: "24",
    height: "24",
    "aria-hidden": "true",
    focusable: "false"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
    d: "M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM5 4.5h14c.3 0 .5.2.5.5v8.4l-3-2.9c-.3-.3-.8-.3-1 0L11.9 14 9 12c-.3-.2-.6-.2-.8 0l-3.6 2.6V5c-.1-.3.1-.5.4-.5zm14 15H5c-.3 0-.5-.2-.5-.5v-2.4l4.1-3 3 1.9c.3.2.7.2.9-.1L16 12l3.5 3.4V19c0 .3-.2.5-.5.5z"
  })),
  /**
   * @see ./edit.js
   */
  edit: _edit__WEBPACK_IMPORTED_MODULE_4__["default"],
  /**
   * @see ./save.js
   */
  save: _save__WEBPACK_IMPORTED_MODULE_5__["default"],
  transforms: {
    from: [{
      type: "shortcode",
      tag: "recent",
      attributes: {
        galleryLabel: {
          type: "string",
          shortcode: _ref => {
            let {
              named: {
                id
              }
            } = _ref;
            return id;
          }
        },
        numberOfImages: {
          type: "string",
          shortcode: _ref2 => {
            let {
              named: {
                max
              }
            } = _ref2;
            return max;
          }
        },
        mode: {
          type: "string",
          shortcode: _ref3 => {
            let {
              named: {
                mode
              }
            } = _ref3;
            return mode;
          }
        },
        galleryTemplate: {
          type: "string",
          shortcode: _ref4 => {
            let {
              named: {
                template
              }
            } = _ref4;
            return template;
          }
        }
      }
    }, {
      type: "block",
      blocks: ["core/shortcode"],
      isMatch: _ref5 => {
        let {
          text
        } = _ref5;
        return text === null || text === void 0 ? void 0 : text.startsWith("[recent");
      },
      transform: _ref6 => {
        let {
          text
        } = _ref6;
        const attributes = text.replace(/\[recent|]|/g, "") //remove the shortcode tags
        .trim() // remove unnecessary spaces before and after
        .split(" "); //split the attributes

        const atts = {};
        attributes.map(item => {
          const split = item.trim().split("=");
          let attName = "";

          // since attributes have new names in the block, we need to match the old ones
          if (split[0] === "id") {
            attName = "galleryLabel";
          } else if (split[0] === "max") {
            attName = "numberOfImages";
          } else if (split[0] === "mode") {
            attName = "mode";
          } else if (split[0] === "template") {
            attName = "galleryTemplate";
          }
          atts[[attName]] = split[1];
        });
        return (0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_1__.createBlock)(name, atts);
      }
    }]
  }
});

/***/ }),

/***/ "./block-editor/blocks/recent-images-block/save.js":
/*!*********************************************************!*\
  !*** ./block-editor/blocks/recent-images-block/save.js ***!
  \*********************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ Save; }
/* harmony export */ });
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__);
/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/
 */


/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-block-editor/#useBlockProps
 */


/**
 * The save function defines the way in which the different attributes should
 * be combined into the final markup, which is then serialized by the block
 * editor into `post_content`.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/block-edit-save/#save
 *
 * @return {WPElement} Element to render.
 */
function Save() {
  return null;
}

/***/ }),

/***/ "./block-editor/gerneral-components/autocomplete/Autocomplete.js":
/*!***********************************************************************!*\
  !*** ./block-editor/gerneral-components/autocomplete/Autocomplete.js ***!
  \***********************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _autocomplete_scss__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./autocomplete.scss */ "./block-editor/gerneral-components/autocomplete/autocomplete.scss");

/**
 * A very simple autocomplete component
 *
 * This is to replace the OOTB Gutenberg Autocomplete component because it is
 * currently broken as of v4.5.1.
 *
 * See Github issue: https://github.com/WordPress/gutenberg/issues/10542
 */

// Load external dependency.



/**
 * Note: The options array should be an array of objects containing labels; i.e.:
 *   [
 *     { labels: 'first' },
 *     { labels: 'second' }
 *   ]
 *
 * @param label Label for the autocomplete
 * @param onChange function to handle onchange event
 * @param options array of objects containing labels
 * @param props
 * @returns {JSX.Element}
 * @constructor
 */
function Autocomplete(_ref) {
  let {
    label,
    preSelected,
    fetch = async () => {
      return [];
    },
    onFocus = () => {},
    onChange = () => {},
    onSelect = () => {},
    ...props
  } = _ref;
  const [value, setValue] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(preSelected ? preSelected : "");
  const [listFocus, setListFocus] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(0);
  const [listFocusOption, setListFocusOption] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(undefined);
  const [open, setOpen] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(false);
  const [internalOptions, setOptions] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)([]);
  const [isLoading, setIsLoading] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(false);

  // Unique ID for the input.
  const inputId = `nextcellent-autocomplete-input`;

  /**
   * Effect executed on load of the component and change of open to reset list focus start
   */
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    if (open) {
      setListFocus(0);
    }
  }, [open]);
  const onClick = async event => {
    setOpen(true);
    setIsLoading(true);
    const json = await fetch("");
    setOptions(json);
    if (json.length > 0) {
      setIsLoading(false);
    } else {
      setOpen(false);
      setIsLoading(false);
    }
  };

  /**
   * Function to handle the onChange event.
   *
   * @param {Event} event
   */
  const onChangeValue = async event => {
    setValue(event.target.value);
    setOpen(true);
    setIsLoading(true);
    const json = await fetch(value);
    setOptions(json);
    if (json.length > 0) {
      setIsLoading(false);
    } else {
      setOpen(false);
      setIsLoading(false);
    }
    onChange(event.target.value);
  };

  /**
   * Function to handle the selection of an option
   *
   * @param {Event} event
   */
  const optionSelect = event => {
    event.stopPropagation();
    event.preventDefault();
    const option = internalOptions[event.target.dataset.option];
    setValue(option.label);
    setOpen(false);
    onSelect(option);
  };

  /**
   * Method that has common funtionality for the arrow key handling
   *
   * @param {[HTMLLIElement]} children
   * @param {string} key
   */
  const handleArrowKey = (children, key) => {
    const target = children[listFocus];
    target.classList.add("focus");
    setListFocusOption(internalOptions[listFocus]);
  };

  /**
   * Method to handle enter and arrow keys
   *
   * @param {Event} event
   */
  const handleKeys = event => {
    const key = ["ArrowDown", "ArrowUp", "Enter"];
    if (key.includes(event.key)) {
      event.stopPropagation();
      event.preventDefault();
      const list = document.getElementsByClassName("nextcellent-autocomplete-options")[0];
      const children = list.childNodes;
      if (event.key === "ArrowDown" && list && list.childElementCount > 0) {
        if (listFocus !== 0) {
          const targetBefore = children[listFocus - 1];
          targetBefore.classList.remove("focus");
        } else if (listFocus === 0) {
          const targetBefore = children[list.childElementCount - 1];
          targetBefore.classList.remove("focus");
        }
        handleArrowKey(children, event.key);
        if (listFocus < list.childElementCount - 1) {
          setListFocus(listFocus + 1);
        } else {
          setListFocus(0);
        }
      }
      if (event.key === "ArrowUp" && list && list.childElementCount > 0) {
        setListFocus(list.childElementCount - 1);
        if (listFocus !== list.childElementCount - 1) {
          const targetBefore = children[listFocus + 1];
          targetBefore.classList.remove("focus");
        } else if (listFocus === list.childElementCount - 1) {
          const targetBefore = children[0];
          targetBefore.classList.remove("focus");
        }
        handleArrowKey(children, event.key);
        if (listFocus - 1 > 0) {
          setListFocus(listFocus - 1);
        } else {
          setListFocus(list.childElementCount - 1);
        }
      }
      if (event.key === "Enter") {
        if (listFocusOption) {
          setValue(listFocusOption.label);
          onSelect(listFocusOption);
        }
        setOpen(false);
      }
    }
  };

  // Return the autocomplete.
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "nextcellent-autocomplete-content"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    htmlFor: inputId
  }, label), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
    id: inputId,
    role: "combobox",
    "aria-autocomplete": "list",
    "aria-expanded": "true",
    "aria-owns": "nextcellent-autocomplete-option-popup",
    type: "text",
    list: inputId,
    value: value,
    onClick: onClick,
    onFocus: onFocus,
    onChange: onChangeValue,
    onKeyDown: handleKeys
  }), open && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("ul", {
    "aria-live": "polite",
    id: "nextcellent-autocomplete-option-popup",
    className: "nextcellent-autocomplete-options"
  }, isLoading && internalOptions.length <= 0 && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("li", {
    className: "loading"
  }), !isLoading && (internalOptions === null || internalOptions === void 0 ? void 0 : internalOptions.map((option, index) => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("li", {
    id: `nextcellent-autocomplete-option-${index}`,
    tabIndex: "-1",
    className: "option",
    onClick: optionSelect,
    key: index,
    "data-option": index
  }, option.label)))));
}
/* harmony default export */ __webpack_exports__["default"] = (Autocomplete);

/***/ }),

/***/ "./block-editor/gerneral-components/mode-select/ModeSelect.js":
/*!********************************************************************!*\
  !*** ./block-editor/gerneral-components/mode-select/ModeSelect.js ***!
  \********************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _mode_scss__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./mode.scss */ "./block-editor/gerneral-components/mode-select/mode.scss");




/**
 *
 * @param value
 * @param onChange
 * @param props
 * @returns {JSX.Element}
 * @constructor
 */
function ModeSelect(_ref) {
  let {
    type = "img",
    value,
    onChange,
    ...props
  } = _ref;
  // Unique ID for the input.
  const inputId = `nextcellent-image-mode-select`;

  // Function to handle the onChange event.
  const onChangeValue = event => {
    onChange(event.target.value);
  };

  // Return the autocomplete.
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "nextcellent-image-mode-select"
  }, type == "img" && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    htmlFor: inputId
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("Effect", "nggallery")), type == "recent" && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    htmlFor: inputId
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("Sort the images", "nggallery")), type == "img" && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("select", {
    name: "modes",
    id: inputId,
    onChange: onChangeValue,
    value: value
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
    value: ""
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("No effect", "nggallery")), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
    value: "watermark"
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("Watermark", "nggallery")), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
    value: "web20"
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("Web 2.0", "nggallery"))), (type == "recent" || type == "random") && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("select", {
    name: "modes",
    id: inputId,
    onChange: onChangeValue,
    value: value
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
    value: ""
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("Upload order", "nggallery")), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
    value: "date"
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("Date taken", "nggallery")), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
    value: "sort"
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("User defined", "nggallery"))));
}
/* harmony default export */ __webpack_exports__["default"] = (ModeSelect);

/***/ }),

/***/ "./block-editor/gerneral-components/number-of-images-input/NumberOfImages.js":
/*!***********************************************************************************!*\
  !*** ./block-editor/gerneral-components/number-of-images-input/NumberOfImages.js ***!
  \***********************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _numberOfImages_scss__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./numberOfImages.scss */ "./block-editor/gerneral-components/number-of-images-input/numberOfImages.scss");



// Load external dependency.


/**
 *
 * @param value
 * @param onChange
 * @param props
 * @returns {JSX.Element}
 * @constructor
 */
function NumberOfImages(_ref) {
  let {
    type = "gallery",
    value,
    onChange,
    ...props
  } = _ref;
  // Unique ID for the id.
  const inputId = `nextcellent-block-number-of-images`;

  // Function to handle the onChange event.
  const onChangeValue = event => {
    onChange(event.target.value);
  };

  // Return the fieldset.
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "nextcellent-number-of-images"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    htmlFor: inputId
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("Number of images", "nggallery")), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
    id: inputId,
    type: "number",
    min: "0",
    step: "1",
    value: value,
    onChange: onChangeValue
  }), type == "gallery" && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("The number of images before pagination is applied. Leave empty or 0 for the default from the settings.", "nggallery")), type == "recent" && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("The number of images that should be displayed.", "nggallery")));
}
/* harmony default export */ __webpack_exports__["default"] = (NumberOfImages);

/***/ }),

/***/ "./block-editor/gerneral-components/template-radio-group/Template.js":
/*!***************************************************************************!*\
  !*** ./block-editor/gerneral-components/template-radio-group/Template.js ***!
  \***************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _template_scss__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./template.scss */ "./block-editor/gerneral-components/template-radio-group/template.scss");




// Load external dependency.


/**
 *
 * @param type album | gallery
 * @param value
 * @param onChange
 * @param props
 * @returns {JSX.Element}
 * @constructor
 */
function Template(_ref) {
  let {
    id,
    type,
    value,
    onChecked,
    ...props
  } = _ref;
  // Unique ID for the input group.
  const inputId = id ? id : `nextcellent-block-template-radio`;

  // Function to handle the onChange event.
  const onCheckedInput = event => {
    onChecked(event.target.value);
  };

  // Return the fieldset.
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("fieldset", {
    "aria-role": "radiogroup",
    className: "nextcellent-template-radio"
  }, (type == "gallery" || type == "albumGallery") && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
    id: "nextcellent-template-type-gallery",
    type: "radio",
    name: inputId,
    checked: value == "gallery",
    onChange: onCheckedInput,
    value: "gallery"
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    htmlFor: "nextcellent-template-type-gallery"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("img", {
    className: "nextcellent-template-type-img",
    src: nggData.pluginUrl + "admin/images/gallery.svg",
    alt: "gallery"
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("Gallery", "nggallery"))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
    id: "nextcellent-template-type-caption",
    type: "radio",
    name: inputId,
    checked: value == "caption",
    onChange: onCheckedInput,
    value: "caption"
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    htmlFor: "nextcellent-template-type-caption"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("img", {
    className: "nextcellent-template-type-img",
    src: nggData.pluginUrl + "admin/images/caption.svg",
    alt: "caption"
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("Caption", "nggallery"))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
    id: "nextcellent-template-type-carousel",
    type: "radio",
    name: inputId,
    checked: value == "carousel",
    onChange: onCheckedInput,
    value: "carousel"
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    htmlFor: "nextcellent-template-type-carousel"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("img", {
    className: "nextcellent-template-type-img",
    src: nggData.pluginUrl + "admin/images/carousel.svg",
    alt: "carousel"
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("Carousel", "nggallery")))), type == "gallery" && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
    id: "nextcellent-template-type-other",
    type: "radio",
    name: inputId,
    checked: value == "other",
    onChange: onCheckedInput,
    value: "other"
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    htmlFor: "nextcellent-template-type-other"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("img", {
    className: "nextcellent-template-type-img",
    src: nggData.pluginUrl + "admin/images/other.svg",
    alt: "other"
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("Other", "nggallery")))), type == "album" && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
    id: "nextcellent-template-type-compact",
    type: "radio",
    name: inputId,
    checked: value == "compact",
    onChange: onCheckedInput,
    value: "compact"
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    htmlFor: "nextcellent-template-type-compact"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("img", {
    className: "nextcellent-template-type-img",
    src: nggData.pluginUrl + "admin/images/compact.svg",
    alt: "compact"
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("Compact", "nggallery"))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
    id: "nextcellent-template-type-extend",
    type: "radio",
    name: inputId,
    checked: value == "extend",
    onChange: onCheckedInput,
    value: "extend"
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    htmlFor: "nextcellent-template-type-extend"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("img", {
    className: "nextcellent-template-type-img",
    src: nggData.pluginUrl + "admin/images/compact.svg",
    alt: "compact"
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("Extend", "nggallery")))));
}
/* harmony default export */ __webpack_exports__["default"] = (Template);

/***/ }),

/***/ "./block-editor/blocks/recent-images-block/editor.scss":
/*!*************************************************************!*\
  !*** ./block-editor/blocks/recent-images-block/editor.scss ***!
  \*************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./block-editor/blocks/recent-images-block/style.scss":
/*!************************************************************!*\
  !*** ./block-editor/blocks/recent-images-block/style.scss ***!
  \************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./block-editor/gerneral-components/autocomplete/autocomplete.scss":
/*!*************************************************************************!*\
  !*** ./block-editor/gerneral-components/autocomplete/autocomplete.scss ***!
  \*************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./block-editor/gerneral-components/mode-select/mode.scss":
/*!****************************************************************!*\
  !*** ./block-editor/gerneral-components/mode-select/mode.scss ***!
  \****************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./block-editor/gerneral-components/number-of-images-input/numberOfImages.scss":
/*!*************************************************************************************!*\
  !*** ./block-editor/gerneral-components/number-of-images-input/numberOfImages.scss ***!
  \*************************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./block-editor/gerneral-components/template-radio-group/template.scss":
/*!*****************************************************************************!*\
  !*** ./block-editor/gerneral-components/template-radio-group/template.scss ***!
  \*****************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "@wordpress/block-editor":
/*!*************************************!*\
  !*** external ["wp","blockEditor"] ***!
  \*************************************/
/***/ (function(module) {

module.exports = window["wp"]["blockEditor"];

/***/ }),

/***/ "@wordpress/blocks":
/*!********************************!*\
  !*** external ["wp","blocks"] ***!
  \********************************/
/***/ (function(module) {

module.exports = window["wp"]["blocks"];

/***/ }),

/***/ "@wordpress/components":
/*!************************************!*\
  !*** external ["wp","components"] ***!
  \************************************/
/***/ (function(module) {

module.exports = window["wp"]["components"];

/***/ }),

/***/ "@wordpress/element":
/*!*********************************!*\
  !*** external ["wp","element"] ***!
  \*********************************/
/***/ (function(module) {

module.exports = window["wp"]["element"];

/***/ }),

/***/ "@wordpress/i18n":
/*!******************************!*\
  !*** external ["wp","i18n"] ***!
  \******************************/
/***/ (function(module) {

module.exports = window["wp"]["i18n"];

/***/ }),

/***/ "@wordpress/server-side-render":
/*!******************************************!*\
  !*** external ["wp","serverSideRender"] ***!
  \******************************************/
/***/ (function(module) {

module.exports = window["wp"]["serverSideRender"];

/***/ }),

/***/ "./block-editor/blocks/recent-images-block/block.json":
/*!************************************************************!*\
  !*** ./block-editor/blocks/recent-images-block/block.json ***!
  \************************************************************/
/***/ (function(module) {

module.exports = JSON.parse('{"$schema":"https://json.schemastore.org/block.json","apiVersion":2,"name":"nggallery/recent-images-block","version":"1.0.0","title":"Recent pictures","category":"nextcellent-blocks","description":"","attributes":{"galleryLabel":{"type":"string"},"numberOfImages":{"type":"number","default":0},"galleryTemplate":{"type":"string","default":"gallery"},"mode":{"type":"string"}},"supports":{"html":false},"textdomain":"nggallery","editorScript":"file:../../../public/blocks/recent-images-block/recent-images-block.js","editorStyle":"file:../../../public/blocks/recent-images-block/recent-images-block.css","style":"file:../../../public/blocks/style-blocks/recent-images-block/style-recent-images-block.css"}');

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = __webpack_modules__;
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/chunk loaded */
/******/ 	!function() {
/******/ 		var deferred = [];
/******/ 		__webpack_require__.O = function(result, chunkIds, fn, priority) {
/******/ 			if(chunkIds) {
/******/ 				priority = priority || 0;
/******/ 				for(var i = deferred.length; i > 0 && deferred[i - 1][2] > priority; i--) deferred[i] = deferred[i - 1];
/******/ 				deferred[i] = [chunkIds, fn, priority];
/******/ 				return;
/******/ 			}
/******/ 			var notFulfilled = Infinity;
/******/ 			for (var i = 0; i < deferred.length; i++) {
/******/ 				var chunkIds = deferred[i][0];
/******/ 				var fn = deferred[i][1];
/******/ 				var priority = deferred[i][2];
/******/ 				var fulfilled = true;
/******/ 				for (var j = 0; j < chunkIds.length; j++) {
/******/ 					if ((priority & 1 === 0 || notFulfilled >= priority) && Object.keys(__webpack_require__.O).every(function(key) { return __webpack_require__.O[key](chunkIds[j]); })) {
/******/ 						chunkIds.splice(j--, 1);
/******/ 					} else {
/******/ 						fulfilled = false;
/******/ 						if(priority < notFulfilled) notFulfilled = priority;
/******/ 					}
/******/ 				}
/******/ 				if(fulfilled) {
/******/ 					deferred.splice(i--, 1)
/******/ 					var r = fn();
/******/ 					if (r !== undefined) result = r;
/******/ 				}
/******/ 			}
/******/ 			return result;
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	!function() {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = function(module) {
/******/ 			var getter = module && module.__esModule ?
/******/ 				function() { return module['default']; } :
/******/ 				function() { return module; };
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	!function() {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = function(exports, definition) {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	!function() {
/******/ 		__webpack_require__.o = function(obj, prop) { return Object.prototype.hasOwnProperty.call(obj, prop); }
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	!function() {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = function(exports) {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/jsonp chunk loading */
/******/ 	!function() {
/******/ 		// no baseURI
/******/ 		
/******/ 		// object to store loaded and loading chunks
/******/ 		// undefined = chunk not loaded, null = chunk preloaded/prefetched
/******/ 		// [resolve, reject, Promise] = chunk loading, 0 = chunk loaded
/******/ 		var installedChunks = {
/******/ 			"blocks/recent-images-block/recent-images-block": 0,
/******/ 			"blocks/recent-images-block/style-recent-images-block": 0
/******/ 		};
/******/ 		
/******/ 		// no chunk on demand loading
/******/ 		
/******/ 		// no prefetching
/******/ 		
/******/ 		// no preloaded
/******/ 		
/******/ 		// no HMR
/******/ 		
/******/ 		// no HMR manifest
/******/ 		
/******/ 		__webpack_require__.O.j = function(chunkId) { return installedChunks[chunkId] === 0; };
/******/ 		
/******/ 		// install a JSONP callback for chunk loading
/******/ 		var webpackJsonpCallback = function(parentChunkLoadingFunction, data) {
/******/ 			var chunkIds = data[0];
/******/ 			var moreModules = data[1];
/******/ 			var runtime = data[2];
/******/ 			// add "moreModules" to the modules object,
/******/ 			// then flag all "chunkIds" as loaded and fire callback
/******/ 			var moduleId, chunkId, i = 0;
/******/ 			if(chunkIds.some(function(id) { return installedChunks[id] !== 0; })) {
/******/ 				for(moduleId in moreModules) {
/******/ 					if(__webpack_require__.o(moreModules, moduleId)) {
/******/ 						__webpack_require__.m[moduleId] = moreModules[moduleId];
/******/ 					}
/******/ 				}
/******/ 				if(runtime) var result = runtime(__webpack_require__);
/******/ 			}
/******/ 			if(parentChunkLoadingFunction) parentChunkLoadingFunction(data);
/******/ 			for(;i < chunkIds.length; i++) {
/******/ 				chunkId = chunkIds[i];
/******/ 				if(__webpack_require__.o(installedChunks, chunkId) && installedChunks[chunkId]) {
/******/ 					installedChunks[chunkId][0]();
/******/ 				}
/******/ 				installedChunks[chunkId] = 0;
/******/ 			}
/******/ 			return __webpack_require__.O(result);
/******/ 		}
/******/ 		
/******/ 		var chunkLoadingGlobal = self["webpackChunknextcellent_gallery"] = self["webpackChunknextcellent_gallery"] || [];
/******/ 		chunkLoadingGlobal.forEach(webpackJsonpCallback.bind(null, 0));
/******/ 		chunkLoadingGlobal.push = webpackJsonpCallback.bind(null, chunkLoadingGlobal.push.bind(chunkLoadingGlobal));
/******/ 	}();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module depends on other loaded chunks and execution need to be delayed
/******/ 	var __webpack_exports__ = __webpack_require__.O(undefined, ["blocks/recent-images-block/style-recent-images-block"], function() { return __webpack_require__("./block-editor/blocks/recent-images-block/index.js"); })
/******/ 	__webpack_exports__ = __webpack_require__.O(__webpack_exports__);
/******/ 	
/******/ })()
;
//# sourceMappingURL=recent-images-block.js.map