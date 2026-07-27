BX.namespace("Aspro.Sku.Popup");
(() => {
  const Popup = class {
    url = "";
    data = "";
    messages = {};
    popupSuffix = "default";

    #popup = null;
    #contentContainer = null;

    constructor(params = {}) {
      try {
        this.checkParamsType(params);
        this.init(params);
      } catch (error) {
        console.error(error);
      }
    }

    checkParamsType(params) {
      if (typeof params !== "object" || !params || !Object.keys(params).length) {
        throw new Error("Bad params");
      }
    }

    init({ url, data, messages, popupSuffix }) {
      url && (this.url = url);
      data && ((this.data = data), (this.data["sessid"] = BX.message("bitrix_sessid")));
      popupSuffix && (this.popupSuffix = popupSuffix);

      this.setMessages(messages);
      this.show();
    }

    setMessages(messages) {
      this.setMessage();
      if (messages && typeof messages === "object") {
        this.messages = Object.assign(this.messages, messages);
      }
    }
    setMessage() {}

    show() {
      this.create();

      this.#popup.setContent(this.createContainer());
      this.#popup.setButtons(this.getButtons());

      this.#popup.show();
    }
    create() {
      if (!(this.#popup instanceof BX.PopupWindow)) {
        this.#popup = BX.PopupWindowManager.create("popup_window_" + this.popupSuffix, null, {
          className: "main-grid-show-popup-animation aspro-popup-window",
          closeIcon: true,
          closeByEsc: true,
          autoHide: true,
          zIndex: 0,
          offsetLeft: 0,
          offsetTop: 0,
          overlay: {
            backgroundColor: "black",
            opacity: "80",
          },
        });
      }
    }
    createContainer() {
      this.#contentContainer = document.createElement("div");
      this.#contentContainer.classList.add("aspro-ui-popup__content");
      this.#contentContainer.innerHTML = this.getPopupContent();

      return this.#contentContainer;
    }
    getPopupContent() {
      return "";
    }
    getButtons() {
      return [];
    }

    sendRequest(btn) {
      BX.ajax({
        url: this.url,
        data: this.data,
        method: "POST",
        dataType: "json",
        onsuccess: (data) => {
          if (data.result === true) {
            if (data.redirect || this.data.redirect) {
              window.location.href = data.redirect || this.data.redirect;
            } else {
              btn.popupWindow.close();
            }

            this.onSuccess?.(data);
          } else {
            this.processError(data.message);

            btn.removeClassName("ui-btn-wait");
          }
        },
        onfailure: () => {
          btn.removeClassName("ui-btn-wait");

          this.onFailure?.(btn);
        },
      });
    }
    processError(messages) {
      this.#contentContainer.innerHTML = "";
      this.mkAlert(messages).renderTo(this.#contentContainer);

      this.#popup.setContent(this.#contentContainer);
      this.#popup.adjustPosition();
    }
    mkAlert(messages) {
      return new BX.UI.Alert({
        text: this.processMessages(messages),
        textCenter: true,
        color: BX.UI.Alert.Color.DANGER,
        customClass: "aspro-ui-alert",
      });
    }
    processMessages(messages) {
      let message = "";
      if (Array.isArray(messages)) {
        messages.forEach(function (value) {
          message += value + "<br>";
        });
      } else {
        message = messages;
      }
      return message;
    }
  };
  BX.Aspro.Sku.Popup = Popup;

  class PopupConfirm extends Popup {
    constructor(params) {
      params.popupSuffix = "confirm";

      super(params);
    }

    setMessage() {
      this.messages = {
        message: BX.message("DELETE_ALL_ELEMENTS"),
        btnOk: BX.message("DELETE_BTN"),
        btnCancel: BX.message("CANCEL_BTN"),
      };
    }

    getPopupContent() {
      return this.messages.message;
    }

    getButtons() {
      const self = this;

      const btnOk = new BX.PopupWindowButton({
        text: this.messages.btnOk,
        className: "ui-btn ui-btn-danger",
        events: {
          click: function () {
            let btn = this;
            btn.addClassName("ui-btn-wait");

            self.sendRequest(btn);
          },
        },
      });

      const btnCancel = new BX.PopupWindowButton({
        text: this.messages.btnCancel,
        className: "ui-btn ui-btn-light",
        events: {
          click: function () {
            this.popupWindow.close();
          },
        },
      });

      return [btnOk, btnCancel];
    }
  }
  BX.Aspro.Sku.Popup.Confirm = PopupConfirm;

  class PopupAlert extends Popup {
    constructor(params) {
      params.popupSuffix = "alert";

      super(params);
    }

    setMessage() {
      this.messages = {
        message: "Error",
      };
    }

    getPopupContent() {
      return this.mkAlert(this.messages.message).getContainer().outerHTML;
    }
  }
  BX.Aspro.Sku.Popup.Alert = PopupAlert;
})();
