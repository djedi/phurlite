const BASE_URL = "https://dld.im";

chrome.tabs.query({ active: true, lastFocusedWindow: true }, (tabs) => {
  let url = tabs[0].url;
  // use `url` here inside the callback because it's asynchronous!
  let urlEl = document.querySelector("#url");
  urlEl.textContent = url;
  window.url = url;
  // createShortLink(url);
});
function ready(fn) {
  if (document.readyState != "loading") {
    fn();
  } else {
    document.addEventListener("DOMContentLoaded", fn);
  }
}
document
  .getElementById("generate-random")
  .addEventListener("click", generateRandom);
document
  .getElementById("generate-custom")
  .addEventListener("click", generateCustom);

function generateRandom() {
  createShortLink(url);
}
function generateCustom() {
  let custom = document.getElementById("custom").value;
  createShortLink(url, custom);
}

function createShortLink(url, custom) {
  var request = new XMLHttpRequest();
  let requestUri = `${BASE_URL}/?u=${encodeURIComponent(url)}`;
  if (custom) {
    requestUri += `&custom=${custom}`;
  }
  request.open("GET", requestUri, true);

  request.onload = function () {
    let shortEl = document.querySelector("#short-link");
    if (this.status >= 200 && this.status < 400) {
      // Success!
      var resp = this.response;
      shortEl.textContent = resp + " copied to clipboard.";
      copyTextToClipboard(resp);
    } else {
      // We reached our target server, but it returned an error
      // Account for 409 error
      var resp = this.response;
      shortEl.textContent = resp;
    }
  };

  request.onerror = function (err) {
    // There was a connection error of some sort
    alert(`error: ${err}`);
  };

  request.send();
}

function copyTextToClipboard(text) {
  //Create a textbox field where we can insert text to.
  var copyFrom = document.createElement("textarea");

  //Set the text content to be the text you wished to copy.
  copyFrom.textContent = text;

  //Append the textbox field into the body as a child.
  //"execCommand()" only works when there exists selected text, and the text is inside
  //document.body (meaning the text is part of a valid rendered HTML element).
  document.body.appendChild(copyFrom);

  //Select all the text!
  copyFrom.select();

  //Execute command
  document.execCommand("copy");

  //(Optional) De-select the text using blur().
  copyFrom.blur();

  //Remove the textbox field from the document.body, so no other JavaScript nor
  //other elements can get access to this.
  document.body.removeChild(copyFrom);
}
