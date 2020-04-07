/*global $ */
'use strict';

$(function() {
  $("#rc_sourceCheck").change(function() {
    if (this.checked)
      $("#sourceConfig").show();
    else
      $("#sourceConfig").hide();
  });
  if (!document.getElementById("rc_sourceCheck").checked) {
    $("#sourceConfig").hide();
  }
  $("#rc_timeoutCheck").change(function() {
    if (this.checked)
      $("#timeoutConfig").show();
    else
      $("#timeoutConfig").hide();
  });
  if (!document.getElementById("rc_timeoutCheck").checked) {
    $("#timeoutConfig").hide();
  }
});
