document.addEventListener("DOMContentLoaded", () => {
  const cache = {
    section: {},
    iblock: {},
    props: {},
  };

  const appendNewOptions = (target, items) => {
    for (id in items) {
      const option = items[id];

      if (option.ITEMS) {
        const nodeOptionGroup = BX.create("optgroup", {
          attrs: {
            label: option.TITLE,
          },
        });

        appendNewOptions(nodeOptionGroup, option.ITEMS);

        target.appendChild(nodeOptionGroup);
      } else {
        const value = option?.id || id
        const text = option?.value || option;

        const nodeSelectOption = BX.create("option", {
          attrs: {
            className: "aspro-sku__form-control__select-option",
            value,
          },
          text,
        });
        target.appendChild(nodeSelectOption);
      }

      target.disabled = false;
    }
  };

  const dropOldOptions = (target) => {
    const nodeSelectOptionsGroups = target.querySelectorAll("optgroup");
    if (nodeSelectOptionsGroups.length) {
      nodeSelectOptionsGroups.forEach((nodeSelectOptionsGroup) => nodeSelectOptionsGroup.remove());
      return;
    }

    const nodeSelectOptions = target.querySelectorAll("option");
    if (nodeSelectOptions.length) {
      nodeSelectOptions.forEach((nodeSelectOption) => nodeSelectOption.remove());
    }
  };

  const resetSelect = (select) => {
    // select.value = select.options?.[0]?.value || "";
    select.value = "";
    select.disabled = !select.options.length;
  };

  const updateSelectField = (element, items = null, valueField = "ID") => {
    const nodeSelectField = element instanceof Node ? element : document.querySelector(element);
    if (!nodeSelectField) {
      return;
    }

    dropOldOptions(nodeSelectField);

    if (items) {
      appendNewOptions(nodeSelectField, items);
    }

    resetSelect(nodeSelectField);
  };

  const updateOffersProperty = (items = null) => {
    const nodeListSelectOffersProperty = document.querySelectorAll(`[data-optioncode="OFFERS_PROPERTY"] select`);
    if (nodeListSelectOffersProperty.length) {
      nodeListSelectOffersProperty.forEach((nodeSelectOffersProperty, index) => {
        const nodeCloseBtn = nodeSelectOffersProperty
          .closest(".aspro-sku__ofers-property-table-cell")
          ?.querySelector("button");

        if (nodeCloseBtn && index > 0) {
          nodeCloseBtn.click();
        } else {
          updateSelectField(nodeSelectOffersProperty, items);
        }
      });
    }

    const nodeButtonAddOffersProperty = document.querySelector(
      '[data-optioncode="OFFERS_PROPERTY"] input[type="button"]'
    );
    if (nodeButtonAddOffersProperty) {
      nodeButtonAddOffersProperty.disabled = !items || !Object.entries(items).length;
    }
  };

  const updateLinkedProperties = (isActive = true) => {
    const nodeFilterItemsOptionContainer = document.querySelector('[data-optioncode="FILTER_ITEMS"]');

    const nodeFilterItemsTable = nodeFilterItemsOptionContainer.querySelector("table");
    if (!nodeFilterItemsTable) {
      return;
    }

    nodeFilterItemsTable.classList.toggle("aspro-control--disabled", !isActive);

    const nodeFilterItemsHash = nodeFilterItemsTable?.dataset?.hash;
    if (nodeFilterItemsHash) {
      window[`MV_${nodeFilterItemsHash}`] = 0;
    }

    const nodeListLinkedPropertiesItem = nodeFilterItemsOptionContainer.querySelectorAll(".linked-property-table__row");
    if (!nodeListLinkedPropertiesItem.length) {
      return;
    }

    for (i = 0; i < nodeListLinkedPropertiesItem.length; i++) {
      if (i > 0) {
        nodeListLinkedPropertiesItem[i].remove();
        continue;
      }

      const nodeInput = nodeListLinkedPropertiesItem[i].querySelector('input[type="text"]');
      if (nodeInput) {
        nodeInput.value = "";
      }

      const nodeTitle = nodeListLinkedPropertiesItem[i].querySelector("span");
      if (nodeTitle) {
        nodeTitle.textContent = "";
      }
    }
  };

  const updateFilterType = (isActive = true) => {
    const nodeListRadioFilterType = document.querySelectorAll(`[data-optioncode="FILTER_TYPE"] select`);
    nodeListRadioFilterType.forEach((nodeRadioFilterType) => {
      nodeRadioFilterType.disabled = !isActive;

      if (nodeRadioFilterType.checked) {
        selectFilterType(nodeRadioFilterType);
      }
    });
  };

  const selectSiteID = async (target) => {
    const nodeSelectSiteID = target.closest(`[data-optioncode="SITE_ID"]`)?.querySelector("select");
    if (!nodeSelectSiteID) {
      return;
    }

    const siteId = nodeSelectSiteID?.value;
    if (!siteId) {
      return;
    }

    updateSelectField(`[data-optioncode="IBLOCK_ID"] select`);
    if (typeof cache.iblock[siteId] === "undefined") {
      const result = await BX.ajax.runAction("aspro:sku.admin.iblock.getIBlocks", {
        data: {
          siteId: nodeSelectSiteID.value,
          lang: new URL(window.location).searchParams.get("lang") || "ru",
        },
      });

      cache.iblock[siteId] = result?.data;
    }

    updateSelectField(`[data-optioncode="IBLOCK_ID"] select`, cache.iblock[siteId]);
    updateSelectField(`[data-optioncode="FILTER_SECTION_ID"] select`);
    updateSelectField(`[data-optioncode="FILTER_PROPERTY"] select`);
    updateLinkedProperties(false);
    updateOffersProperty();
  };

  const selectIBlockID = async (target) => {
    const nodeSelectIBlockID = target.closest(`[data-optioncode="IBLOCK_ID"]`)?.querySelector("select");
    if (!nodeSelectIBlockID) {
      return;
    }

    const iblockId = nodeSelectIBlockID.value;

    updateFilterType();
    updateLinkedProperties();

    updateSelectField(`[data-optioncode="FILTER_SECTION_ID"] select`);
    if (typeof cache.section[iblockId] === "undefined") {
      const result = await BX.ajax.runAction("aspro:sku.admin.iblock.getSections", {
        data: {
          iblockId,
        },
      });

      const sections = result?.data?.SECTIONS;
      const order = result?.data?.ORDER;

      cache.section[iblockId] = [];
      order.forEach(key => {
        cache.section[iblockId].push({
          id: key,
          value: sections[key]
        })
      })
    }

    updateSelectField(`[data-optioncode="FILTER_PROPERTY"] select`);
    if (typeof cache.props[iblockId] === "undefined") {
      const result = await BX.ajax.runAction("aspro:sku.admin.iblock.getIblockProperties", {
        data: {
          iblockId,
        },
      });

      cache.props[iblockId] = result?.data;
    }

    updateSelectField(`[data-optioncode="FILTER_SECTION_ID"] select`, cache.section[iblockId]);
    updateSelectField(`[data-optioncode="FILTER_PROPERTY"] select`, cache.props[iblockId]);
    updateOffersProperty(cache.props[iblockId]);

    const nodeTemplate = document.querySelector(".aspro-sku__ofers-property-template");
    if (!nodeTemplate) {
      return;
    }

    const nodeTableRowTemplate = nodeTemplate.content.cloneNode(true).children[0];
    if (!nodeTableRowTemplate) {
      return;
    }

    const nodeSelect = nodeTableRowTemplate.querySelector("select");
    if (nodeSelect) {
      updateSelectField(nodeSelect, cache.props[iblockId]);
      nodeSelect.disabled = false;
      nodeTemplate.content.replaceChildren(nodeTableRowTemplate);
    }

    // updateSections(iblockId);
    // updateProperties(iblockId);
  };

  const selectFilterType = (target) => {
    const nodeSelectFilterType = target.closest(`[data-optioncode="FILTER_TYPE"]`)?.querySelector("input:checked");
    if (nodeSelectFilterType) {
      const nodeTableRowFilterItems = document.querySelector('[data-optioncode="FILTER_ITEMS"]');
      const nodeTableRowFilterSectionID = document.querySelector('[data-optioncode="FILTER_SECTION_ID"]');
      const nodeTableRowFilterProperty = document.querySelector('[data-optioncode="FILTER_PROPERTY"]');

      const isManual = nodeSelectFilterType.value === "MANUAL";

      nodeTableRowFilterItems?.toggleAttribute("hidden", !isManual);
      nodeTableRowFilterSectionID?.toggleAttribute("hidden", isManual);
      nodeTableRowFilterProperty?.toggleAttribute("hidden", isManual);
    }
  };

  document.addEventListener("change", (event) => {
    selectSiteID(event.target);
    selectIBlockID(event.target);
    selectFilterType(event.target);
  });
});

function openLinkedElementsWindow(href) {
  const url = new URL(location.origin + href);
  const iblockId = document.querySelector('[data-optioncode="IBLOCK_ID"] select')?.value;

  if (iblockId) {
    url.searchParams.append("IBLOCK_ID", iblockId);
  }

  jsUtils.OpenWindow(url.href, 900, 700);
}
