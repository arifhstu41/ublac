(function() {

  var iframe = document.createElement("iframe");
  iframe.style.position = "fixed";
  iframe.style.width = "100vw";
  iframe.style.height = "100vh";
  iframe.style.top = 0;
  iframe.style.left = 0;
  iframe.style.border = "none";
  iframe.style.zIndex = "999999999999";

  iframe.src = "<?php echo admin_url('perfex_popup/install/install_iframe/'). $popup->popup_key; ?>";

  document.body.appendChild(iframe);

  function removeIframe(event) {
    if (event?.data !== "tfgremoveiframe"){
      return;
    }
    iframe.parentNode.removeChild(iframe);
  }
  window.addEventListener("message", removeIframe, false);

  function fnRedirect(event) {
    if (event?.data?.name !== "tfgfnredirect" || !event?.data?.target_url){
      return;
    }
    window.location = event.data.target_url;
  }
  window.addEventListener("message", fnRedirect, false);

  function iframeReady(event){
    if (event.data == "tfgiframeready"){
      iframe.contentWindow.postMessage({
        name: "tfginitpopup",
        parent_url: window.location.href,
        parent_inner_width: window.innerWidth,
        local_display_frequency: localStorage.getItem(`popup_display_frequency_<?php echo  $popup->id; ?>`),
        session_display_frequency: sessionStorage.getItem(`popup_display_frequency_<?php echo  $popup->id; ?>`),
        session_popup_hover: sessionStorage.getItem(`popup_hover_<?php echo  $popup->id; ?>`),
        local_should_show: localStorage.getItem(`popup_<?php echo  $popup->id; ?>_converted`),
      }, '*');
    }
  }
  window.addEventListener("message", iframeReady, false);

  function setLocalstorage(event){
    if (event?.data?.name == "tfgsetlocalstorage"){
      localStorage.setItem(event?.data?.key, event?.data?.value);
    }
  }
  window.addEventListener("message", setLocalstorage, false);

  function setSessionstorage(event){
    if (event?.data?.name == "tfgsetsessionstorage"){
      sessionStorage.setItem(event?.data?.key, event?.data?.value);
    }
  }
  window.addEventListener("message", setSessionstorage, false);
})();