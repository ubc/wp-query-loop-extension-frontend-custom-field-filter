import * as __WEBPACK_EXTERNAL_MODULE__wordpress_interactivity_8e89b257__ from "@wordpress/interactivity";
/******/ var __webpack_modules__ = ({

/***/ "@wordpress/interactivity":
/*!*******************************************!*\
  !*** external "@wordpress/interactivity" ***!
  \*******************************************/
/***/ ((module) => {

module.exports = __WEBPACK_EXTERNAL_MODULE__wordpress_interactivity_8e89b257__;

/***/ }),

/***/ "@wordpress/interactivity-router":
/*!**************************************************!*\
  !*** external "@wordpress/interactivity-router" ***!
  \**************************************************/
/***/ ((module) => {

module.exports = import("@wordpress/interactivity-router");;

/***/ })

/******/ });
/************************************************************************/
/******/ // The module cache
/******/ var __webpack_module_cache__ = {};
/******/ 
/******/ // The require function
/******/ function __webpack_require__(moduleId) {
/******/ 	// Check if module is in cache
/******/ 	var cachedModule = __webpack_module_cache__[moduleId];
/******/ 	if (cachedModule !== undefined) {
/******/ 		return cachedModule.exports;
/******/ 	}
/******/ 	// Create a new module (and put it into the cache)
/******/ 	var module = __webpack_module_cache__[moduleId] = {
/******/ 		// no module.id needed
/******/ 		// no module.loaded needed
/******/ 		exports: {}
/******/ 	};
/******/ 
/******/ 	// Execute the module function
/******/ 	__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 
/******/ 	// Return the exports of the module
/******/ 	return module.exports;
/******/ }
/******/ 
/************************************************************************/
/******/ /* webpack/runtime/make namespace object */
/******/ (() => {
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = (exports) => {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/ })();
/******/ 
/************************************************************************/
var __webpack_exports__ = {};
// This entry needs to be wrapped in an IIFE because it needs to be isolated against other modules in the chunk.
(() => {
/*!*********************!*\
  !*** ./src/view.js ***!
  \*********************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_interactivity__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/interactivity */ "@wordpress/interactivity");
/**
 * WordPress dependencies
 */

let didRunInitially = false;
const updateURLParameter = (url, urlParameters) => {
  const newUrl = new URL(url);
  urlParameters.forEach(urlParameter => {
    newUrl.searchParams.set(urlParameter.identifier, urlParameter.value);
  });
  return newUrl;
};
(0,_wordpress_interactivity__WEBPACK_IMPORTED_MODULE_0__.store)('ctlt-query-custom-field-filter', {
  actions: {
    onChangeField: event => {
      event.preventDefault();
      const context = (0,_wordpress_interactivity__WEBPACK_IMPORTED_MODULE_0__.getContext)();

      // Check the element tag, if it's a checkbox, get the all the checked values.
      if (event.target.tagName === 'INPUT' && event.target.type === 'checkbox') {
        // Get the name of the checkbox
        const checkboxName = event.target.name;
        // Get all the checked values
        const checkedValues = document.querySelectorAll(`input[name="${checkboxName}"]:checked`);
        // Add the checked values to the selecteds array
        context.selected = Array.from(checkedValues).map(checkbox => checkbox.value);
        console.log(context.selected);
      } else {
        context.selected = event.target.value;
      }
    }
  },
  callbacks: {
    navigateToDestination: function* () {
      const {
        ref
      } = (0,_wordpress_interactivity__WEBPACK_IMPORTED_MODULE_0__.getElement)();
      const {
        selected
      } = (0,_wordpress_interactivity__WEBPACK_IMPORTED_MODULE_0__.getContext)();
      if (null === ref) {
        return;
      }
      if (!didRunInitially) {
        didRunInitially = true;
        return; // Skip the first run on node creation
      }
      const queryRef = ref.closest('.wp-block-query[data-wp-router-region]');
      console.log(ref);
      const {
        actions
      } = yield Promise.resolve(/*! import() */).then(__webpack_require__.bind(__webpack_require__, /*! @wordpress/interactivity-router */ "@wordpress/interactivity-router"));
      let navigateTo = updateURLParameter(window.location, [{
        identifier: queryRef.getAttribute('data-wp-router-region') + '-cf-' + ref.getAttribute('filter-id'),
        value: Array.isArray(selected) ? selected.join(',') : selected
      }, {
        identifier: queryRef.getAttribute('data-wp-router-region') + '-page',
        value: '1'
      }]);
      yield actions.navigate(navigateTo);
    }
  }
});
})();


//# sourceMappingURL=view.js.map