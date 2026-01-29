(self["webpackChunk"] = self["webpackChunk"] || []).push([["delete_modal"],{

/***/ "./assets/js/delete_modal.js"
/*!***********************************!*\
  !*** ./assets/js/delete_modal.js ***!
  \***********************************/
() {

var deleteExpenseId = null;

// Global functions for onclick handlers
window.showDeleteConfirm = function (expenseId) {
  deleteExpenseId = expenseId;
  var modal = document.getElementById('deleteModal');
  modal.style.display = 'flex';
};
window.hideDeleteConfirm = function () {
  var modal = document.getElementById('deleteModal');
  modal.style.display = 'none';
  deleteExpenseId = null;
};
window.confirmDelete = function () {
  if (deleteExpenseId) {
    var form = document.createElement('form');
    form.method = 'POST';
    form.action = '/expenses/delete/' + deleteExpenseId;
    document.body.appendChild(form);
    form.submit();
  }
};

// Close modal when clicking outside
document.addEventListener('DOMContentLoaded', function () {
  var modal = document.getElementById('deleteModal');
  if (modal) {
    modal.addEventListener('click', function (e) {
      if (e.target === this) {
        window.hideDeleteConfirm();
      }
    });
  }
});

/***/ }

},
/******/ __webpack_require__ => { // webpackRuntimeModules
/******/ var __webpack_exec__ = (moduleId) => (__webpack_require__(__webpack_require__.s = moduleId))
/******/ var __webpack_exports__ = (__webpack_exec__("./assets/js/delete_modal.js"));
/******/ }
]);
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiZGVsZXRlX21vZGFsLmpzIiwibWFwcGluZ3MiOiI7Ozs7Ozs7O0FBQUEsSUFBSUEsZUFBZSxHQUFHLElBQUk7O0FBRTFCO0FBQ0FDLE1BQU0sQ0FBQ0MsaUJBQWlCLEdBQUcsVUFBVUMsU0FBUyxFQUFFO0VBQzVDSCxlQUFlLEdBQUdHLFNBQVM7RUFDM0IsSUFBTUMsS0FBSyxHQUFHQyxRQUFRLENBQUNDLGNBQWMsQ0FBQyxhQUFhLENBQUM7RUFDcERGLEtBQUssQ0FBQ0csS0FBSyxDQUFDQyxPQUFPLEdBQUcsTUFBTTtBQUNoQyxDQUFDO0FBRURQLE1BQU0sQ0FBQ1EsaUJBQWlCLEdBQUcsWUFBWTtFQUNuQyxJQUFNTCxLQUFLLEdBQUdDLFFBQVEsQ0FBQ0MsY0FBYyxDQUFDLGFBQWEsQ0FBQztFQUNwREYsS0FBSyxDQUFDRyxLQUFLLENBQUNDLE9BQU8sR0FBRyxNQUFNO0VBQzVCUixlQUFlLEdBQUcsSUFBSTtBQUMxQixDQUFDO0FBRURDLE1BQU0sQ0FBQ1MsYUFBYSxHQUFHLFlBQVk7RUFDL0IsSUFBSVYsZUFBZSxFQUFFO0lBQ2pCLElBQU1XLElBQUksR0FBR04sUUFBUSxDQUFDTyxhQUFhLENBQUMsTUFBTSxDQUFDO0lBQzNDRCxJQUFJLENBQUNFLE1BQU0sR0FBRyxNQUFNO0lBQ3BCRixJQUFJLENBQUNHLE1BQU0sR0FBRyxtQkFBbUIsR0FBR2QsZUFBZTtJQUNuREssUUFBUSxDQUFDVSxJQUFJLENBQUNDLFdBQVcsQ0FBQ0wsSUFBSSxDQUFDO0lBQy9CQSxJQUFJLENBQUNNLE1BQU0sQ0FBQyxDQUFDO0VBQ2pCO0FBQ0osQ0FBQzs7QUFFRDtBQUNBWixRQUFRLENBQUNhLGdCQUFnQixDQUFDLGtCQUFrQixFQUFFLFlBQU07RUFDaEQsSUFBTWQsS0FBSyxHQUFHQyxRQUFRLENBQUNDLGNBQWMsQ0FBQyxhQUFhLENBQUM7RUFDcEQsSUFBSUYsS0FBSyxFQUFFO0lBQ1BBLEtBQUssQ0FBQ2MsZ0JBQWdCLENBQUMsT0FBTyxFQUFFLFVBQVVDLENBQUMsRUFBRTtNQUN6QyxJQUFJQSxDQUFDLENBQUNDLE1BQU0sS0FBSyxJQUFJLEVBQUU7UUFDbkJuQixNQUFNLENBQUNRLGlCQUFpQixDQUFDLENBQUM7TUFDOUI7SUFDSixDQUFDLENBQUM7RUFDTjtBQUNKLENBQUMsQ0FBQyxDIiwic291cmNlcyI6WyJ3ZWJwYWNrOi8vLy4vYXNzZXRzL2pzL2RlbGV0ZV9tb2RhbC5qcyJdLCJzb3VyY2VzQ29udGVudCI6WyJsZXQgZGVsZXRlRXhwZW5zZUlkID0gbnVsbDtcblxuLy8gR2xvYmFsIGZ1bmN0aW9ucyBmb3Igb25jbGljayBoYW5kbGVyc1xud2luZG93LnNob3dEZWxldGVDb25maXJtID0gZnVuY3Rpb24gKGV4cGVuc2VJZCkge1xuICAgIGRlbGV0ZUV4cGVuc2VJZCA9IGV4cGVuc2VJZDtcbiAgICBjb25zdCBtb2RhbCA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdkZWxldGVNb2RhbCcpO1xuICAgIG1vZGFsLnN0eWxlLmRpc3BsYXkgPSAnZmxleCc7XG59O1xuXG53aW5kb3cuaGlkZURlbGV0ZUNvbmZpcm0gPSBmdW5jdGlvbiAoKSB7XG4gICAgY29uc3QgbW9kYWwgPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgnZGVsZXRlTW9kYWwnKTtcbiAgICBtb2RhbC5zdHlsZS5kaXNwbGF5ID0gJ25vbmUnO1xuICAgIGRlbGV0ZUV4cGVuc2VJZCA9IG51bGw7XG59O1xuXG53aW5kb3cuY29uZmlybURlbGV0ZSA9IGZ1bmN0aW9uICgpIHtcbiAgICBpZiAoZGVsZXRlRXhwZW5zZUlkKSB7XG4gICAgICAgIGNvbnN0IGZvcm0gPSBkb2N1bWVudC5jcmVhdGVFbGVtZW50KCdmb3JtJyk7XG4gICAgICAgIGZvcm0ubWV0aG9kID0gJ1BPU1QnO1xuICAgICAgICBmb3JtLmFjdGlvbiA9ICcvZXhwZW5zZXMvZGVsZXRlLycgKyBkZWxldGVFeHBlbnNlSWQ7XG4gICAgICAgIGRvY3VtZW50LmJvZHkuYXBwZW5kQ2hpbGQoZm9ybSk7XG4gICAgICAgIGZvcm0uc3VibWl0KCk7XG4gICAgfVxufTtcblxuLy8gQ2xvc2UgbW9kYWwgd2hlbiBjbGlja2luZyBvdXRzaWRlXG5kb2N1bWVudC5hZGRFdmVudExpc3RlbmVyKCdET01Db250ZW50TG9hZGVkJywgKCkgPT4ge1xuICAgIGNvbnN0IG1vZGFsID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ2RlbGV0ZU1vZGFsJyk7XG4gICAgaWYgKG1vZGFsKSB7XG4gICAgICAgIG1vZGFsLmFkZEV2ZW50TGlzdGVuZXIoJ2NsaWNrJywgZnVuY3Rpb24gKGUpIHtcbiAgICAgICAgICAgIGlmIChlLnRhcmdldCA9PT0gdGhpcykge1xuICAgICAgICAgICAgICAgIHdpbmRvdy5oaWRlRGVsZXRlQ29uZmlybSgpO1xuICAgICAgICAgICAgfVxuICAgICAgICB9KTtcbiAgICB9XG59KTtcbiJdLCJuYW1lcyI6WyJkZWxldGVFeHBlbnNlSWQiLCJ3aW5kb3ciLCJzaG93RGVsZXRlQ29uZmlybSIsImV4cGVuc2VJZCIsIm1vZGFsIiwiZG9jdW1lbnQiLCJnZXRFbGVtZW50QnlJZCIsInN0eWxlIiwiZGlzcGxheSIsImhpZGVEZWxldGVDb25maXJtIiwiY29uZmlybURlbGV0ZSIsImZvcm0iLCJjcmVhdGVFbGVtZW50IiwibWV0aG9kIiwiYWN0aW9uIiwiYm9keSIsImFwcGVuZENoaWxkIiwic3VibWl0IiwiYWRkRXZlbnRMaXN0ZW5lciIsImUiLCJ0YXJnZXQiXSwiaWdub3JlTGlzdCI6W10sInNvdXJjZVJvb3QiOiIifQ==