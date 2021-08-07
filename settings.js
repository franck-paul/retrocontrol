/*global $ */
'use strict';

$(function () {
  $('#rc_sourceCheck').on('change', function () {
    if (this.checked) $('#sourceConfig').show();
    else $('#sourceConfig').hide();
  });
  if (!document.getElementById('rc_sourceCheck').checked) {
    $('#sourceConfig').hide();
  }
  $('#rc_timeoutCheck').on('change', function () {
    if (this.checked) $('#timeoutConfig').show();
    else $('#timeoutConfig').hide();
  });
  if (!document.getElementById('rc_timeoutCheck').checked) {
    $('#timeoutConfig').hide();
  }
});
