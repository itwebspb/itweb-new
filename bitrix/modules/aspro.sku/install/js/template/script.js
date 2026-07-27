(() => {
  let nodeActiveTooltip = null;

  const tooltipsHandler = (event) => {
    const nodeButtonTooltipTrigger = event.target.closest(".sku-props-hint__icon");
    const nodeTooltip = event.target.closest(".sku-props-hint__tooltip");

    if (nodeButtonTooltipTrigger) {
      const newTooltip = nodeButtonTooltipTrigger.parentElement.querySelector(".sku-props-hint__tooltip");

      if (nodeActiveTooltip && nodeActiveTooltip !== newTooltip) {
        nodeActiveTooltip.classList.remove("_active");
      }

      if (newTooltip) {
        if (newTooltip === nodeActiveTooltip) {
          newTooltip.classList.remove("_active");
          nodeActiveTooltip = null;
        } else {
          newTooltip.classList.add("_active");
          nodeActiveTooltip = newTooltip;
        }
      }
    } else if (!nodeTooltip) {
      if (nodeActiveTooltip) {
        nodeActiveTooltip.classList.remove("_active");
        nodeActiveTooltip = null;
      }
    }
  };

  const headerPropsAnchorHandler = (event) => {
    if (!event.target.closest(".sku-props--compact")) {
      return;
    }

    if (!event.target.closest(".sku-props-list__value")) {
      return;
    }

    const nodeListSkuPropsContainer = document.querySelectorAll(".sku-props:not(.sku-props--compact)");
    if (!nodeListSkuPropsContainer.length) {
      return;
    }

    nodeListSkuPropsContainer.forEach((nodeSkuPropsContainer) => {
      if (!nodeSkuPropsContainer.checkVisibility()) {
        return;
      }

      const elementRect = nodeSkuPropsContainer.getBoundingClientRect();
      const offset = elementRect.top + window.scrollY;

      window.scrollTo({
        top: offset - 150,
        behavior: "smooth",
      });
    });
  }

  document.addEventListener("click", (event) => {
    tooltipsHandler(event);
    headerPropsAnchorHandler(event);
  });

  document.addEventListener("keydown", (event) => {
    if (event.code === "Escape" && nodeActiveTooltip) {
      nodeActiveTooltip.classList.remove("_active");
      nodeActiveTooltip = null;
    }
  });

  BX.addCustomEvent("onAsproDetailHeaderHTML", (eventdata) => {
    const nodeSkuPropsContainer = document.querySelector(".sku-props");
    if (!nodeSkuPropsContainer) {
      return;
    }

    const nodeListActiveSkuProp = nodeSkuPropsContainer.querySelectorAll(".sku-props-list__value.active");
    if (!nodeListActiveSkuProp) {
      return;
    }

    eventdata.sku = '<div class="sku-props sku-props--compact">';

    nodeListActiveSkuProp.forEach((nodeActiveSkuProp) => {
      eventdata.sku += nodeActiveSkuProp.outerHTML;
    });

    eventdata.sku += "</div>";
  });
})();
