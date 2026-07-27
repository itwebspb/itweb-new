document.addEventListener("DOMContentLoaded", () => {
  const menuBlock = document.querySelector(".menu_top_block[data-visible-count]");
  if (!menuBlock) {
    return;
  }

  const visibleCount = parseInt(menuBlock.dataset.visibleCount) || 10;
  const action = BX?.Aspro?.Utils?.Ajax?.getControllerNamespace() + "CatalogSubmenu.getItems";

  const buildSubsectionItem = (subsection, index) => {
    const li = document.createElement("li");
    const a = document.createElement("a");
    const span = document.createElement("span");

    li.className = `menu_item${index + 1 > visibleCount ? " collapsed" : ""}`;
    a.className = "parent1 section1";
    a.href = subsection.url;
    span.textContent = subsection.name;

    a.appendChild(span);
    li.appendChild(a);

    return li;
  };

  const buildSubsectionList = (subsections) => {
    const ul = document.createElement("ul");

    ul.className = "dropdown toggle_menu";
    subsections.forEach((subsection, index) => ul.appendChild(buildSubsectionItem(subsection, index)));

    if (subsections.length > visibleCount) {
      const more = document.createElement("li");
      const count = subsections.length - visibleCount;

      more.innerHTML = `<span class="more_items with_dropdown">${BX.message("S_MORE_ITEMS")} ${count}</span>`;
      ul.appendChild(more);
    }

    return ul;
  };

  const bindMoreItems = (parent) => {
    parent.querySelectorAll(".more_items.with_dropdown").forEach((btn) => {
      btn.addEventListener("click", () => {
        btn
          .closest("ul")
          .querySelectorAll(".menu_item.collapsed")
          .forEach((el) => el.classList.remove("collapsed"));
        btn.closest("li").style.display = "none";
      });
    });
  };

  const recalcDropdownPosition = (menuItem) => {
    const dropdown = menuItem.querySelector(".dropdown-block");
    if (!dropdown || getComputedStyle(dropdown).display === "none" || !menuItem.classList.contains("m_line")) {
      return;
    }

    let mt = menuItem.offsetHeight;
    const pos = BX.pos(dropdown, true);

    if (pos.height) {
      let cmt = parseInt(dropdown.style.marginTop);
      cmt = isNaN(cmt) ? 0 : cmt;

      const bottom = pos.bottom - cmt - mt;
      if (bottom >= window.innerHeight) {
        mt = mt + bottom - window.innerHeight;
      }

      const top = pos.top - cmt - mt;
      if (top < 0) {
        mt = mt + top;
      }
    }

    dropdown.style.marginTop = "-" + mt + "px";
  };

  const injectSubsections = (menuItem, sections) => {
    sections.forEach((section) => {
      if (!section.children.length) {
        return;
      }

      const sectionEl = menuItem.querySelector(`[data-l2-id="${section.id}"]`);
      if (!sectionEl) {
        return;
      }

      sectionEl.classList.add("has-childs");
      sectionEl.insertBefore(buildSubsectionList(section.children), sectionEl.querySelector(".clearfix"));
    });

    bindMoreItems(menuItem);
  };

  document.querySelectorAll(".ajax-submenu-parent").forEach((menuItem) => {
    let loaded = false;

    menuItem.addEventListener("mouseenter", () => {
      if (loaded) {
        return;
      }

      loaded = true;

      const dropdown = menuItem.querySelector(".dropdown-block");
      if (dropdown) {
        dropdown.style.display = "none";
      }

      BX.ajax
        .runAction(action, { data: { sectionId: menuItem.dataset.sectionId } })
        .then((response) => {
          const sections = response.data.items;
          if (sections?.length) {
            injectSubsections(menuItem, sections);
          }
          if (dropdown) {
            if (menuItem.matches(":hover")) {
              dropdown.style.display = "block";
              recalcDropdownPosition(menuItem);
            } else {
              dropdown.style.display = "";
            }
          }
        })
        .catch(() => {
          loaded = false;
          if (dropdown) {
            dropdown.style.display = "";
          }
        });
    });
  });
});
