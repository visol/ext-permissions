/**
 * @module @visol/Permissions/ChangeActions.js
 */
const ChangeActions = (() => {
  const selectElement = document.getElementById('depth');
  const depthBaseUrl = selectElement.dataset.depthBaseUrl;

  const init = () => {
    const select = document.getElementById("depth");
    select.addEventListener("change", (e) => {
      const value = e.target.value;
      const url = depthBaseUrl.replace("__DEPTH__", value);
      window.location.href = url;
      return false;
    });
  };

  init();
})();

export default ChangeActions;
