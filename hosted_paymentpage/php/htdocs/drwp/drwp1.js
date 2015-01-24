

function lunchboxOpen(lunchID,lunchName) {
document.getElementById('lunch_' + lunchID).style.display = "block";
document.getElementById('clasp_' + lunchID).innerHTML="<a href=\"javascript:lunchboxClose('" + lunchID + "','" + lunchName + "');\">Close " + lunchName + " ...</a>";
 }
 function lunchboxClose(lunchID,lunchName) {
document.getElementById('lunch_' + lunchID).style.display = "none";
document.getElementById('clasp_' + lunchID).innerHTML="<a href=\"javascript:lunchboxOpen('" + lunchID + "','" + lunchName + "');\">Open " + lunchName + "  ...</a>";
 }
 
 
