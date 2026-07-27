BX.namespace("Aspro.Sku.DetailManager");

(() => {
  let _containerId = "";

  const _sendRequest = ({ url, data }) => {
    return BX.ajax({
      url: url,
      data: data,
      method: "POST",
      dataType: "json",
      preparePost: false,
      onsuccess: (data) => {
        if (data?.result === true) {
          BX.Aspro.Sku.DetailManager.onSuccessRequest?.(data);
        } else {
          showAlert(data?.message);
        }
      },
      onfailure: () => {
        BX.Aspro.Sku.DetailManager.onFailureRequest?.();
      },
    });
  };

  const showAlert = (message) => {
    new BX.Aspro.Sku.Popup.Alert({ messages: { message } });

    BX.Aspro.Sku.DetailManager.onFailureRequest?.();
  };

  const clearBtnLoadingState = () => {
    document.querySelector(".adm-btn-load-img")?.remove();

    document.querySelector(".adm-detail-content-btns .adm-btn-load")?.classList.remove("adm-btn-load");

    const btnDisabled = document.querySelector(".adm-detail-content-btns .ui-btn[disabled]");
    if (!btnDisabled) {
      return;
    }
    btnDisabled.removeAttribute("disabled");
    btnDisabled.disabled = false;
  };

  BX.Aspro.Sku.DetailManager = {
    get id() {
      return document.querySelector(`#${_containerId} form`);
    },
    set id(id) {
      _containerId = id;
    },

    send(action) {
      const url = this.id.action;
      const data = new FormData(this.id);

      if (!url || !data) {
        return;
      }

      this.action = action;

      _sendRequest({ url, data });
    },

    onFailureRequest() {
      setTimeout(clearBtnLoadingState);
    },

    onSuccessRequest(data) {
      this[`${this.action}Action`]?.(data);
    },

    applyAction(data) {
      const detailUrl = this.id.dataset?.detailUrl;
      if (!detailUrl || !data.ID) {
        return;
      }

      const url = new URL(detailUrl, location.origin);
      url.searchParams.append("id", data.ID);

      const activeTab = this.id.querySelector("[id$=active_tab");
      if (activeTab?.value) {
        url.searchParams.append(activeTab.name, activeTab.value);
      }

      this.redirect(url);
    },

    redirect(url) {
      location.href = url;
    },

    saveAction(data) {
      const listUrl = this.id.dataset?.listUrl;
      if (!listUrl) {
        return;
      }

      this.redirect(listUrl);
    },

    saveaddAction(data) {
      const detailUrl = this.id.dataset?.detailUrl;
      if (!detailUrl) {
        return;
      }

      this.redirect(detailUrl);
    },
  };
})();
