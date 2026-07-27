BX.namespace("Aspro.Sku.GridManager");

(() => {
  let _gridId = "";

  const _getGridInstance = (id) => {
    let gridObject = BX.Main.gridManager.getById(id);

    if (!gridObject?.hasOwnProperty("instance")) {
      console.log("Grid instance not found");
      return false;
    }

    return gridObject.instance;
  };

  const _sendRequest = ({ url, data }) => {
    data["sessid"] = BX.message("bitrix_sessid");

    return BX.ajax({
      url: url,
      data: data,
      method: "POST",
      dataType: "json",
      onsuccess: (data) => {
        if (data.result === true) {
          BX.Aspro.Sku.GridManager.onSuccessRequest?.(data);
        } else {
          showAlert(data.message);
        }
      },
      onfailure: () => {
        BX.Aspro.Sku.GridManager.onFailureRequest?.();
      },
    });
  };

  const showAlert = (message) => {
    new BX.Aspro.Sku.Popup.Alert({ messages: { message } });

    BX.Aspro.Sku.GridManager.onFailureRequest?.();
  };

  BX.Aspro.Sku.GridManager = {
    get id() {
      return _gridId;
    },
    set id(id) {
      _gridId = id;
    },

    send(url, id, data = {}) {
      if (!url || !id) {
        return;
      }

      _sendRequest({ url, data: { ID: id, ...data } });
    },

    sendWithConfirm(url, id, data = {}, messages = {}) {
      if (!url || !id) {
        return;
      }

      const confirm = new BX.Aspro.Sku.Popup.Confirm({ url, data: { ID: id, ...data }, messages });
      confirm.onSuccess = (data) => {
        _getGridInstance(this.id)?.reloadTable(null, null);
      };
    },

    onSuccessRequest(data) {
      _getGridInstance(this.id)?.reloadTable(null, null);
    },
  };
})();
