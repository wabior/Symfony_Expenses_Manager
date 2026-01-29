(self["webpackChunk"] = self["webpackChunk"] || []).push([["payment_status"],{

/***/ "./assets/js/payment_status.js"
/*!*************************************!*\
  !*** ./assets/js/payment_status.js ***!
  \*************************************/
() {

var initPaymentStatus = function initPaymentStatus() {
  var paymentStatusSelect = document.getElementById('paymentStatus');
  var paymentDateInput = document.getElementById('paymentDate');

  // Only proceed if both elements exist (only in add form, not edit)
  if (!paymentStatusSelect || !paymentDateInput) {
    return;
  }
  var paymentDateContainer = paymentDateInput.closest('div');
  var togglePaymentDate = function togglePaymentDate() {
    if (paymentStatusSelect.value === 'unpaid') {
      paymentDateContainer.style.display = 'none';
    } else {
      paymentDateContainer.style.display = 'block';
    }
  };
  paymentStatusSelect.addEventListener('change', togglePaymentDate);

  // Initialize the visibility based on the initial status
  togglePaymentDate();
};
document.addEventListener('DOMContentLoaded', initPaymentStatus);

/***/ }

},
/******/ __webpack_require__ => { // webpackRuntimeModules
/******/ var __webpack_exec__ = (moduleId) => (__webpack_require__(__webpack_require__.s = moduleId))
/******/ var __webpack_exports__ = (__webpack_exec__("./assets/js/payment_status.js"));
/******/ }
]);
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoicGF5bWVudF9zdGF0dXMuanMiLCJtYXBwaW5ncyI6Ijs7Ozs7Ozs7QUFBQSxJQUFNQSxpQkFBaUIsR0FBRyxTQUFwQkEsaUJBQWlCQSxDQUFBLEVBQVM7RUFDNUIsSUFBTUMsbUJBQW1CLEdBQUdDLFFBQVEsQ0FBQ0MsY0FBYyxDQUFDLGVBQWUsQ0FBQztFQUNwRSxJQUFNQyxnQkFBZ0IsR0FBR0YsUUFBUSxDQUFDQyxjQUFjLENBQUMsYUFBYSxDQUFDOztFQUUvRDtFQUNBLElBQUksQ0FBQ0YsbUJBQW1CLElBQUksQ0FBQ0csZ0JBQWdCLEVBQUU7SUFDM0M7RUFDSjtFQUVBLElBQU1DLG9CQUFvQixHQUFHRCxnQkFBZ0IsQ0FBQ0UsT0FBTyxDQUFDLEtBQUssQ0FBQztFQUU1RCxJQUFNQyxpQkFBaUIsR0FBRyxTQUFwQkEsaUJBQWlCQSxDQUFBLEVBQVM7SUFDNUIsSUFBSU4sbUJBQW1CLENBQUNPLEtBQUssS0FBSyxRQUFRLEVBQUU7TUFDeENILG9CQUFvQixDQUFDSSxLQUFLLENBQUNDLE9BQU8sR0FBRyxNQUFNO0lBQy9DLENBQUMsTUFBTTtNQUNITCxvQkFBb0IsQ0FBQ0ksS0FBSyxDQUFDQyxPQUFPLEdBQUcsT0FBTztJQUNoRDtFQUNKLENBQUM7RUFFRFQsbUJBQW1CLENBQUNVLGdCQUFnQixDQUFDLFFBQVEsRUFBRUosaUJBQWlCLENBQUM7O0VBRWpFO0VBQ0FBLGlCQUFpQixDQUFDLENBQUM7QUFDdkIsQ0FBQztBQUVETCxRQUFRLENBQUNTLGdCQUFnQixDQUFDLGtCQUFrQixFQUFFWCxpQkFBaUIsQ0FBQyxDIiwic291cmNlcyI6WyJ3ZWJwYWNrOi8vLy4vYXNzZXRzL2pzL3BheW1lbnRfc3RhdHVzLmpzIl0sInNvdXJjZXNDb250ZW50IjpbImNvbnN0IGluaXRQYXltZW50U3RhdHVzID0gKCkgPT4ge1xuICAgIGNvbnN0IHBheW1lbnRTdGF0dXNTZWxlY3QgPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgncGF5bWVudFN0YXR1cycpO1xuICAgIGNvbnN0IHBheW1lbnREYXRlSW5wdXQgPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgncGF5bWVudERhdGUnKTtcblxuICAgIC8vIE9ubHkgcHJvY2VlZCBpZiBib3RoIGVsZW1lbnRzIGV4aXN0IChvbmx5IGluIGFkZCBmb3JtLCBub3QgZWRpdClcbiAgICBpZiAoIXBheW1lbnRTdGF0dXNTZWxlY3QgfHwgIXBheW1lbnREYXRlSW5wdXQpIHtcbiAgICAgICAgcmV0dXJuO1xuICAgIH1cblxuICAgIGNvbnN0IHBheW1lbnREYXRlQ29udGFpbmVyID0gcGF5bWVudERhdGVJbnB1dC5jbG9zZXN0KCdkaXYnKTtcblxuICAgIGNvbnN0IHRvZ2dsZVBheW1lbnREYXRlID0gKCkgPT4ge1xuICAgICAgICBpZiAocGF5bWVudFN0YXR1c1NlbGVjdC52YWx1ZSA9PT0gJ3VucGFpZCcpIHtcbiAgICAgICAgICAgIHBheW1lbnREYXRlQ29udGFpbmVyLnN0eWxlLmRpc3BsYXkgPSAnbm9uZSc7XG4gICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICBwYXltZW50RGF0ZUNvbnRhaW5lci5zdHlsZS5kaXNwbGF5ID0gJ2Jsb2NrJztcbiAgICAgICAgfVxuICAgIH07XG5cbiAgICBwYXltZW50U3RhdHVzU2VsZWN0LmFkZEV2ZW50TGlzdGVuZXIoJ2NoYW5nZScsIHRvZ2dsZVBheW1lbnREYXRlKTtcblxuICAgIC8vIEluaXRpYWxpemUgdGhlIHZpc2liaWxpdHkgYmFzZWQgb24gdGhlIGluaXRpYWwgc3RhdHVzXG4gICAgdG9nZ2xlUGF5bWVudERhdGUoKTtcbn07XG5cbmRvY3VtZW50LmFkZEV2ZW50TGlzdGVuZXIoJ0RPTUNvbnRlbnRMb2FkZWQnLCBpbml0UGF5bWVudFN0YXR1cyk7XG4iXSwibmFtZXMiOlsiaW5pdFBheW1lbnRTdGF0dXMiLCJwYXltZW50U3RhdHVzU2VsZWN0IiwiZG9jdW1lbnQiLCJnZXRFbGVtZW50QnlJZCIsInBheW1lbnREYXRlSW5wdXQiLCJwYXltZW50RGF0ZUNvbnRhaW5lciIsImNsb3Nlc3QiLCJ0b2dnbGVQYXltZW50RGF0ZSIsInZhbHVlIiwic3R5bGUiLCJkaXNwbGF5IiwiYWRkRXZlbnRMaXN0ZW5lciJdLCJpZ25vcmVMaXN0IjpbXSwic291cmNlUm9vdCI6IiJ9