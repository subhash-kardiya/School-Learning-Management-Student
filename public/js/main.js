const toggleBtn = document.getElementById("sidebarToggle");
const desktopToggleBtn = document.getElementById("sidebarDesktopToggle");
const sidebar = document.querySelector(".sidebar");
const body = document.body;
const SIDEBAR_COLLAPSE_KEY = "sidebar_collapsed";
const SIDEBAR_ANIM_MS = 320;

// Create an overlay div for clicking outside to close
const overlay = document.createElement("div");
overlay.className = "sidebar-overlay";
body.appendChild(overlay);

function setDesktopSidebarState(collapsed) {
    if (!sidebar) return;
    body.classList.toggle("sidebar-collapsed", collapsed);
    if (!collapsed) {
        body.classList.remove("has-floating-menu");
    }
    sidebar.querySelectorAll(".collapse").forEach((panel) => {
        panel.classList.remove("floating-open");
        if (!collapsed) {
            panel.style.top = "";
            panel.style.left = "";
            panel.style.width = "";
            panel.style.maxHeight = "";
        } else if (panel.classList.contains("show")) {
            bootstrap.Collapse.getOrCreateInstance(panel, { toggle: false }).hide();
        }
    });
    localStorage.setItem(SIDEBAR_COLLAPSE_KEY, collapsed ? "1" : "0");
}

if (window.innerWidth > 768 && localStorage.getItem(SIDEBAR_COLLAPSE_KEY) === "1") {
    setDesktopSidebarState(true);
}

if (desktopToggleBtn) {
    desktopToggleBtn.addEventListener("click", () => {
        if (window.innerWidth <= 768) return;
        const willCollapse = !body.classList.contains("sidebar-collapsed");
        body.classList.add("sidebar-transitioning");
        body.classList.toggle("sidebar-collapsing", willCollapse);
        body.classList.toggle("sidebar-expanding", !willCollapse);
        setDesktopSidebarState(willCollapse);
        window.setTimeout(() => {
            body.classList.remove("sidebar-transitioning", "sidebar-collapsing", "sidebar-expanding");
        }, SIDEBAR_ANIM_MS);
    });
}

if (toggleBtn) {
    toggleBtn.addEventListener("click", () => {
        sidebar.classList.toggle("active");
        overlay.classList.toggle("active");
    });
}

// Close sidebar when clicking the overlay
overlay.addEventListener("click", () => {
    sidebar.classList.remove("active");
    overlay.classList.remove("active");
});

window.addEventListener("resize", () => {
    if (window.innerWidth <= 768) {
        body.classList.remove("sidebar-collapsed");
    } else if (localStorage.getItem(SIDEBAR_COLLAPSE_KEY) === "1") {
        body.classList.add("sidebar-collapsed");
    }
});

// Initialize Bootstrap offcanvas component
const offcanvasElement = document.getElementById("offcanvasWithBothOptions");
// Explicitly set the backdrop property during initialization
if (offcanvasElement) {
    const offcanvas = new bootstrap.Offcanvas(offcanvasElement, {
        backdrop: true, // Ensure the backdrop is properly set
    });
}

// Highlight the active menu item based on the current URL
const currentPath = window.location.pathname;
const menuItems = document.querySelectorAll(".sidebar a");

function isMenuPathActive(itemHref, activePath) {
    if (!itemHref || itemHref.startsWith("#")) return false;
    try {
        const itemUrl = new URL(itemHref, window.location.origin);
        const itemPath = itemUrl.pathname.replace(/\/+$/, "") || "/";
        const normalizedActivePath = activePath.replace(/\/+$/, "") || "/";
        if (itemPath === "/") return normalizedActivePath === "/";
        return normalizedActivePath === itemPath || normalizedActivePath.startsWith(`${itemPath}/`);
    } catch {
        return false;
    }
}

menuItems.forEach((item) => {
    if (isMenuPathActive(item.getAttribute("href") || item.href, currentPath)) {
        item.classList.add("active");
    }
});

if (sidebar) {
    const sidebarPanels = Array.from(sidebar.querySelectorAll(".collapse"));
    const sidebarTriggers = Array.from(sidebar.querySelectorAll('[data-bs-toggle="collapse"]'));
    const floatingOverlay = document.createElement("div");
    floatingOverlay.className = "floating-menu-overlay";
    body.appendChild(floatingOverlay);
    let hoverCloseTimer = null;
    let activeFloatingPanel = null;
    let hoveredTrigger = null;
    let hoveredPanel = null;

    const getPanelForTrigger = (trigger) => {
        if (!trigger) return null;
        const href = trigger.getAttribute("href");
        if (!href || !href.startsWith("#")) return null;
        return sidebar.querySelector(href);
    };

    const getOpenFloatingPanels = () => Array.from(sidebar.querySelectorAll(".collapse.floating-open"));

    const enforceSingleFloatingPanel = (preferredPanel = null) => {
        const openPanels = getOpenFloatingPanels();
        if (openPanels.length <= 1) {
            if (openPanels.length === 1) {
                activeFloatingPanel = openPanels[0];
            } else if (!openPanels.length) {
                activeFloatingPanel = null;
            }
            return;
        }

        const keepPanel =
            preferredPanel && openPanels.includes(preferredPanel)
                ? preferredPanel
                : openPanels[openPanels.length - 1];

        openPanels.forEach((panel) => {
            if (panel !== keepPanel) closeFloatingPanel(panel);
        });
        activeFloatingPanel = keepPanel;
    };

    const clearPanelPosition = (panel) => {
        panel.style.position = "";
        panel.style.top = "";
        panel.style.left = "";
        panel.style.width = "";
        panel.style.maxHeight = "";
    };

    const positionPanel = (panel, trigger) => {
        if (!panel || !trigger) return;
        const triggerRect = trigger.getBoundingClientRect();
        const sidebarRect = sidebar.getBoundingClientRect();
        const panelHeight = Math.max(panel.scrollHeight + 20, 220);
        const viewportPadding = 16;
        const maxAllowedTop = Math.max(viewportPadding, window.innerHeight - panelHeight - viewportPadding);
        const preferredTop = triggerRect.top - 10;
        const computedTop = Math.min(Math.max(viewportPadding, preferredTop), maxAllowedTop);
        panel.style.position = "fixed";
        panel.style.top = `${computedTop}px`;
        panel.style.left = `${Math.round(sidebarRect.right)}px`;
        panel.style.width = "286px";
        panel.style.maxHeight = `${Math.max(240, window.innerHeight - computedTop - viewportPadding)}px`;
    };

    const isCollapsedMode = () => body.classList.contains("sidebar-collapsed");

    const syncTriggerStates = () => {
        sidebarTriggers.forEach((trigger) => {
            const panel = getPanelForTrigger(trigger);
            if (!panel) return;
            const isOpen = isCollapsedMode() ? panel.classList.contains("floating-open") : panel.classList.contains("show");
            trigger.setAttribute("aria-expanded", isOpen ? "true" : "false");
            trigger.classList.toggle("submenu-open", isOpen);
        });
    };

    const syncFloatingOverlayState = () => {
        const hasOpenPanel = !!activeFloatingPanel && activeFloatingPanel.classList.contains("floating-open");
        body.classList.toggle("has-floating-menu", isCollapsedMode() && hasOpenPanel);
    };

    const closeFloatingPanel = (panel) => {
        if (!panel) return;
        panel.classList.remove("floating-open");
        clearPanelPosition(panel);
        if (activeFloatingPanel === panel) {
            activeFloatingPanel = null;
        }
    };

    const closeAllFloatingPanels = () => {
        getOpenFloatingPanels().forEach((panel) => closeFloatingPanel(panel));
        sidebarPanels.forEach((panel) => {
            if (!panel.classList.contains("floating-open")) {
                clearPanelPosition(panel);
            }
        });
        hoveredTrigger = null;
        hoveredPanel = null;
        syncTriggerStates();
        syncFloatingOverlayState();
    };

    const openFloatingPanel = (trigger, panel) => {
        getOpenFloatingPanels().forEach((otherPanel) => {
            if (otherPanel !== panel) closeFloatingPanel(otherPanel);
        });
        positionPanel(panel, trigger);
        panel.classList.add("floating-open");
        activeFloatingPanel = panel;
        enforceSingleFloatingPanel(panel);
        syncTriggerStates();
        syncFloatingOverlayState();
        window.requestAnimationFrame(() => {
            enforceSingleFloatingPanel(panel);
            syncTriggerStates();
            syncFloatingOverlayState();
        });
    };

    const scheduleClosePanels = () => {
        if (hoverCloseTimer) {
            window.clearTimeout(hoverCloseTimer);
        }
        hoverCloseTimer = window.setTimeout(() => {
            if (hoveredTrigger || hoveredPanel) return;
            const keepOpen = sidebarPanels.some((panel) => {
                if (!panel.classList.contains("floating-open")) return false;
                const trigger = sidebar.querySelector(`[href="#${panel.id}"]`);
                const panelHovered = panel.matches(":hover");
                const triggerHovered = trigger ? trigger.matches(":hover") : false;
                return panelHovered || triggerHovered;
            });
            if (keepOpen) return;
            closeAllFloatingPanels();
        }, 260);
    };

    const cancelScheduledClose = () => {
        if (!hoverCloseTimer) return;
        window.clearTimeout(hoverCloseTimer);
        hoverCloseTimer = null;
    };

    document.addEventListener("click", (event) => {
        if (!isCollapsedMode()) return;
        const target = event.target;
        if (!(target instanceof Element)) return;
        if (target.closest(".sidebar")) return;
        closeAllFloatingPanels();
    });

    floatingOverlay.addEventListener("click", () => {
        closeAllFloatingPanels();
    });

    sidebarTriggers.forEach((trigger) => {
        trigger.addEventListener("click", (event) => {
            if (!isCollapsedMode()) return;
            if (body.classList.contains("sidebar-transitioning")) return;
            const panel = getPanelForTrigger(trigger);
            if (!panel) return;
            event.preventDefault();
            event.stopImmediatePropagation();
            cancelScheduledClose();
            const isOpen = panel.classList.contains("floating-open");
            if (isOpen) {
                closeFloatingPanel(panel);
                syncTriggerStates();
                syncFloatingOverlayState();
            } else {
                openFloatingPanel(trigger, panel);
            }
        });

        trigger.addEventListener("mouseenter", () => {
            if (!isCollapsedMode()) return;
            const panel = getPanelForTrigger(trigger);
            if (!panel) return;
            hoveredTrigger = trigger;
            cancelScheduledClose();
            openFloatingPanel(trigger, panel);
        });

        trigger.addEventListener("mouseleave", (event) => {
            if (!isCollapsedMode()) return;
            hoveredTrigger = null;
            const panel = getPanelForTrigger(trigger);
            const nextTarget = event.relatedTarget;
            if (panel && nextTarget instanceof Element && panel.contains(nextTarget)) {
                cancelScheduledClose();
                return;
            }
            scheduleClosePanels();
        });
    });

    sidebarPanels.forEach((panel) => {
        panel.querySelectorAll("a").forEach((submenuLink) => {
            submenuLink.addEventListener("click", () => {
                if (!isCollapsedMode()) return;
                closeAllFloatingPanels();
            });
        });

        panel.addEventListener("mouseenter", () => {
            if (!isCollapsedMode()) return;
            hoveredPanel = panel;
            cancelScheduledClose();
        });

        panel.addEventListener("click", () => {
            if (!isCollapsedMode()) return;
            cancelScheduledClose();
        });


        panel.addEventListener("mouseleave", (event) => {
            if (!isCollapsedMode()) return;
            hoveredPanel = null;
            const nextTarget = event.relatedTarget;
            const trigger = sidebar.querySelector(`[href="#${panel.id}"]`);
            if (trigger && nextTarget instanceof Element && trigger.contains(nextTarget)) {
                cancelScheduledClose();
                return;
            }
            scheduleClosePanels();
        });

        panel.addEventListener("show.bs.collapse", () => {
            if (!isCollapsedMode()) return;
            panel.classList.remove("show");
            enforceSingleFloatingPanel(activeFloatingPanel);
            syncTriggerStates();
            syncFloatingOverlayState();
        });
    });

    window.addEventListener("resize", () => {
        if (!isCollapsedMode()) {
            closeAllFloatingPanels();
            syncFloatingOverlayState();
            return;
        }
        if (activeFloatingPanel && activeFloatingPanel.classList.contains("floating-open")) {
            const trigger = sidebar.querySelector(`[href="#${activeFloatingPanel.id}"]`);
            if (trigger) positionPanel(activeFloatingPanel, trigger);
        }
        syncTriggerStates();
        syncFloatingOverlayState();
    });
}

const subjectOptionCache = new WeakMap();

function getSelectPlaceholder(select, fallback) {
    if (!select) return fallback;
    const firstOption = select.options && select.options.length ? select.options[0] : null;
    return firstOption ? firstOption.textContent : fallback;
}

async function fetchJson(url) {
    const res = await fetch(url);
    if (!res.ok) {
        throw new Error(`Failed request: ${url}`);
    }
    return res.json();
}

function populateSelect(select, rows, placeholder, selectedValue = "") {
    if (!select) return;
    const previous = selectedValue !== "" ? selectedValue : select.value;
    select.innerHTML = "";
    const defaultOption = document.createElement("option");
    defaultOption.value = "";
    defaultOption.textContent = placeholder;
    select.appendChild(defaultOption);

    rows.forEach((row) => {
        const option = document.createElement("option");
        option.value = row.id;
        option.textContent = row.name;
        if (String(previous) === String(row.id)) {
            option.selected = true;
        }
        select.appendChild(option);
    });
}

async function loadClassesByYear(yearId, classSelect, selectedClass = "") {
    if (!classSelect) return;
    const placeholder = getSelectPlaceholder(classSelect, "All Classes");
    populateSelect(classSelect, [], placeholder, "");
    if (!yearId) {
        classSelect.disabled = false;
        return;
    }

    try {
        const classes = await fetchJson(`/classes/by-year/${yearId}`);
        populateSelect(classSelect, classes, placeholder, selectedClass);
    } catch (e) {
        // noop
    } finally {
        classSelect.disabled = false;
    }
}

async function loadSectionsByClass(classId, sectionSelect, selectedSection = "") {
    if (!sectionSelect) return;
    const placeholder = getSelectPlaceholder(sectionSelect, "All Sections");
    populateSelect(sectionSelect, [], placeholder, "");
    if (!classId) {
        sectionSelect.disabled = true;
        return;
    }

    try {
        const sections = await fetchJson(`/sections/by-class/${classId}`);
        populateSelect(sectionSelect, sections, placeholder, selectedSection);
    } catch (e) {
        // noop
    } finally {
        sectionSelect.disabled = false;
    }
}

async function loadTeacherBySubject(subjectId, teacherSelect, classId = "", sectionId = "") {
    if (!teacherSelect || !subjectId) return;
    const params = new URLSearchParams();
    if (classId) params.set("class_id", classId);
    if (sectionId) params.set("section_id", sectionId);
    const url = `/teachers/by-subject/${subjectId}${params.toString() ? `?${params.toString()}` : ""}`;

    try {
        const payload = await fetchJson(url);
        if (payload && payload.id) {
            teacherSelect.value = String(payload.id);
            teacherSelect.dispatchEvent(new Event("change"));
        }
    } catch (e) {
        // noop
    }
}

function bindSubjectOptionsByClass(container) {
    const classSelect = container.querySelector('select[name="class_id"]');
    const subjectSelect = container.querySelector('select[name="subject_id"]');
    if (!classSelect || !subjectSelect) return;

    const hasClassMap = Array.from(subjectSelect.options).some((opt) => opt.dataset.classId);
    if (!hasClassMap) return;

    if (!subjectOptionCache.has(subjectSelect)) {
        subjectOptionCache.set(
            subjectSelect,
            Array.from(subjectSelect.options).map((opt) => opt.cloneNode(true)),
        );
    }

    const filterSubjects = () => {
        const selectedClass = classSelect.value;
        const currentSubject = subjectSelect.value;
        const source = subjectOptionCache.get(subjectSelect) || [];

        subjectSelect.innerHTML = "";
        source.forEach((opt) => {
            if (opt.value === "") {
                subjectSelect.appendChild(opt.cloneNode(true));
                return;
            }
            const optionClass = opt.dataset.classId;
            if (!selectedClass || !optionClass || optionClass === selectedClass) {
                subjectSelect.appendChild(opt.cloneNode(true));
            }
        });

        if (Array.from(subjectSelect.options).some((opt) => String(opt.value) === String(currentSubject))) {
            subjectSelect.value = currentSubject;
        }
    };

    if (classSelect.dataset.subjectClassBound !== "1") {
        classSelect.addEventListener("change", filterSubjects);
        classSelect.dataset.subjectClassBound = "1";
    }

    filterSubjects();
}

document.addEventListener("DOMContentLoaded", () => {
    // Global header context filter
    const globalYear = document.getElementById("globalAcademicYearSelect");
    const globalClass = document.getElementById("globalClassSelect");
    const globalSection = document.getElementById("globalSectionSelect");
    if (globalYear && globalClass) {
        globalYear.addEventListener("change", async () => {
            await loadClassesByYear(globalYear.value, globalClass);
            if (globalSection) {
                await loadSectionsByClass(globalClass.value, globalSection);
            }
        });
    }
    if (globalClass && globalSection) {
        globalClass.addEventListener("change", () => {
            loadSectionsByClass(globalClass.value, globalSection);
        });
    }

    // Generic year->class and class->section cascade across all forms
    const formContainers = Array.from(document.querySelectorAll("form"));
    formContainers.forEach((container) => {
        const yearSelect = container.querySelector('select[name="academic_year_id"]');
        const classSelect = container.querySelector('select[name="class_id"], select#class_id');
        if (classSelect && classSelect.id === "globalClassSelect") {
            return;
        }
        const sectionSelect =
            container.querySelector('select[name="section_id"]') ||
            container.querySelector("select#section_id");

        if (yearSelect && classSelect && classSelect.dataset.yearClassBound !== "1") {
            yearSelect.addEventListener("change", async () => {
                await loadClassesByYear(yearSelect.value, classSelect);
                if (sectionSelect) {
                    await loadSectionsByClass(classSelect.value, sectionSelect);
                }
            });
            classSelect.dataset.yearClassBound = "1";
        }

        if (classSelect && sectionSelect && sectionSelect.dataset.dynamicBound !== "1") {
            sectionSelect.dataset.dynamicBound = "1";
            classSelect.addEventListener("change", () => {
                loadSectionsByClass(classSelect.value, sectionSelect);
            });
        }

        bindSubjectOptionsByClass(container);

        // Initial cascade sync for forms that already have values
        if (yearSelect && classSelect && yearSelect.value) {
            loadClassesByYear(yearSelect.value, classSelect, classSelect.value).then(() => {
                if (sectionSelect) {
                    loadSectionsByClass(classSelect.value, sectionSelect, sectionSelect.value);
                }
            });
        } else if (classSelect && sectionSelect && classSelect.value) {
            loadSectionsByClass(classSelect.value, sectionSelect, sectionSelect.value);
        }
    });

    // Subject -> Teacher auto mapping (opt-in by data attribute)
    const autoTeacherSelects = Array.from(document.querySelectorAll('select[name="teacher_id"][data-auto-map-teacher="1"]'));
    autoTeacherSelects.forEach((teacherSelect) => {
        const container = teacherSelect.closest("form") || document;
        const subjectSelect = container.querySelector('select[name="subject_id"]');
        const classSelect = container.querySelector('select[name="class_id"]');
        const sectionSelect = container.querySelector('select[name="section_id"]');
        if (!subjectSelect) return;

        const syncTeacher = () => {
            const subjectId = subjectSelect.value;
            const classId = classSelect ? classSelect.value : "";
            const sectionId = sectionSelect ? sectionSelect.value : "";
            if (subjectId) {
                loadTeacherBySubject(subjectId, teacherSelect, classId, sectionId);
            }
        };

        subjectSelect.addEventListener("change", syncTeacher);
        if (classSelect) classSelect.addEventListener("change", syncTeacher);
        if (sectionSelect) sectionSelect.addEventListener("change", syncTeacher);
        syncTeacher();
    });

    // Initial sync for already selected global filters
    if (globalYear && globalClass && globalYear.value) {
        loadClassesByYear(globalYear.value, globalClass, globalClass.value).then(() => {
            if (globalSection) {
                loadSectionsByClass(globalClass.value, globalSection, globalSection.value);
            }
        });
    } else if (globalClass && globalSection && globalClass.value) {
        loadSectionsByClass(globalClass.value, globalSection, globalSection.value);
    }
});
