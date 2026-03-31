import { createClient } from "@supabase/supabase-js";

const TOKEN_KEY = "aics_supabase_access_token";
const DASHBOARD_CACHE_KEY = "aics_dashboard_tab_cache_v1";
const DASHBOARD_ALLOWED_TABS = new Set([
    "dashboard",
    "user-management",
    "audit-log",
    "system-activity",
    "sms-settings",
    "system-settings",
    "account-settings",
]);

const appConfig = window.__AICS_SUPABASE__ ?? null;

function setStatus(message, isError = false) {
    const el = document.getElementById("auth-status");
    if (!el) return;

    el.classList.remove(
        "hidden",
        "border-red-200",
        "bg-red-50",
        "text-red-700",
        "border-emerald-200",
        "bg-emerald-50",
        "text-emerald-700",
    );
    if (isError) {
        el.classList.add("border-red-200", "bg-red-50", "text-red-700");
    } else {
        el.classList.add(
            "border-emerald-200",
            "bg-emerald-50",
            "text-emerald-700",
        );
    }
    el.textContent = message;
}

async function validateBackendSession(token) {
    const response = await fetch("/auth/session", {
        headers: {
            Accept: "application/json",
            Authorization: `Bearer ${token}`,
        },
    });

    const payload = await response.json();

    if (!response.ok) {
        throw new Error(payload.message ?? "Session validation failed.");
    }

    return payload;
}

function initLoginFlow() {
    const form = document.getElementById("supabase-login-form");
    if (!form || !appConfig?.url || !appConfig?.anonKey) {
        return;
    }

    const supabase = createClient(appConfig.url, appConfig.anonKey, {
        auth: {
            persistSession: false,
        },
    });

    form.addEventListener("submit", async (event) => {
        event.preventDefault();

        const formData = new FormData(form);
        const email = String(formData.get("email") ?? "");
        const password = String(formData.get("password") ?? "");

        try {
            setStatus("Signing in...");
            const { data, error } = await supabase.auth.signInWithPassword({
                email,
                password,
            });

            if (error || !data.session?.access_token) {
                throw new Error(
                    error?.message ?? "Unable to sign in to Supabase.",
                );
            }

            const token = data.session.access_token;
            localStorage.setItem(TOKEN_KEY, token);

            await validateBackendSession(token);
            setStatus("Sign in successful. Redirecting to dashboard...");
            window.location.assign("/dashboard");
        } catch (err) {
            setStatus(
                err instanceof Error
                    ? err.message
                    : "Unexpected authentication error.",
                true,
            );
        }
    });
}

function initDashboardFlow() {
    const output = document.getElementById("guard-output");
    const sessionBtn = document.getElementById("check-session");
    const adminBtn = document.getElementById("check-admin");
    const logoutBtn = document.getElementById("logout-btn");
    const sidebarName = document.getElementById("sidebar-user-name");
    const sidebarRole = document.getElementById("sidebar-user-role");

    if (
        !logoutBtn &&
        !sidebarName &&
        !sidebarRole &&
        !sessionBtn &&
        !adminBtn &&
        !output
    ) {
        return;
    }

    const token = localStorage.getItem(TOKEN_KEY);
    if (!token) {
        if (output) {
            output.textContent = "No token found. Redirecting to login...";
        }
        window.location.assign("/login");
        return;
    }

    const setUserField = (id, value) => {
        const el = document.getElementById(id);
        if (el) {
            el.textContent = value ?? "-";
        }
    };

    const setSidebarIdentity = (user) => {
        const fullNameEl = document.getElementById("sidebar-user-name");
        const roleEl = document.getElementById("sidebar-user-role");

        const firstName =
            typeof user?.first_name === "string" ? user.first_name.trim() : "";
        const lastName =
            typeof user?.last_name === "string" ? user.last_name.trim() : "";
        const fullName =
            `${firstName} ${lastName}`.trim() ||
            user?.email ||
            "Authenticated User";
        const role =
            typeof user?.role === "string"
                ? user.role.replace(/_/g, " ")
                : "staff";

        if (fullNameEl) fullNameEl.textContent = fullName;
        if (roleEl) roleEl.textContent = role;
    };

    const callProtected = async (endpoint) => {
        const response = await fetch(endpoint, {
            headers: {
                Accept: "application/json",
                Authorization: `Bearer ${token}`,
            },
        });

        const payload = await response.json();

        if (response.status === 401) {
            localStorage.removeItem(TOKEN_KEY);
            window.location.assign("/login");
            return;
        }

        if (endpoint === "/auth/session" && response.ok) {
            const user = payload.user ?? {};
            setUserField("user-id", user.user_id);
            setUserField("user-email", user.email);
            setUserField("user-role", user.role);
            setUserField("user-status", user.status);
            setSidebarIdentity(user);
        }

        if (output) {
            output.textContent = JSON.stringify(
                {
                    endpoint,
                    status: response.status,
                    payload,
                },
                null,
                2,
            );
        }
    };

    const initDynamicDashboardTabs = () => {
        const contentEl = document.getElementById("dashboard-content");
        if (!contentEl) {
            return;
        }

        const pageTitleEl = document.getElementById("dashboard-page-title");
        const loadingEl = document.getElementById("dashboard-content-loading");
        const endpointTemplate = contentEl.dataset.contentEndpointTemplate;
        const dashboardBaseUrl =
            contentEl.dataset.dashboardBaseUrl ?? "/dashboard";
        const readStorage = () => {
            try {
                return JSON.parse(
                    sessionStorage.getItem(DASHBOARD_CACHE_KEY) ?? "{}",
                );
            } catch {
                return {};
            }
        };
        const writeStorage = (cacheMap) => {
            sessionStorage.setItem(
                DASHBOARD_CACHE_KEY,
                JSON.stringify(cacheMap),
            );
        };
        const normalizeTab = (tab) =>
            typeof tab === "string" && DASHBOARD_ALLOWED_TABS.has(tab)
                ? tab
                : "dashboard";
        const setLoading = (isLoading) => {
            if (!loadingEl) {
                return;
            }

            loadingEl.classList.toggle("hidden", !isLoading);
        };
        const setActiveTabVisuals = (activeTab) => {
            document
                .querySelectorAll("[data-dashboard-tab]")
                .forEach((node) => {
                    if (!(node instanceof HTMLElement)) {
                        return;
                    }

                    const tabKey = node.dataset.dashboardTab;
                    const baseClass = node.dataset.tabBaseClass;
                    const activeClass = node.dataset.tabActiveClass;
                    const inactiveClass = node.dataset.tabInactiveClass;

                    if (
                        !tabKey ||
                        !baseClass ||
                        !activeClass ||
                        !inactiveClass
                    ) {
                        return;
                    }

                    node.className = `${baseClass} ${tabKey === activeTab ? activeClass : inactiveClass}`;
                });
        };
        const getTitleFromTab = (tab) => {
            const map = {
                dashboard: "Dashboard",
                "user-management": "User Management",
                "audit-log": "Audit Log",
                "system-activity": "System Activity",
                "sms-settings": "SMS Settings",
                "system-settings": "System Settings",
                "account-settings": "Account Settings",
            };

            return map[tab] ?? "Dashboard";
        };

        if (!endpointTemplate || !endpointTemplate.includes("__TAB__")) {
            return;
        }

        let memoryCache = readStorage();
        const initialTab = normalizeTab(
            new URLSearchParams(window.location.search).get("tab") ??
                contentEl.dataset.activeTab ??
                "dashboard",
        );
        if (!memoryCache[initialTab]) {
            memoryCache[initialTab] = {
                html: contentEl.innerHTML,
                title:
                    pageTitleEl?.textContent?.trim() ||
                    getTitleFromTab(initialTab),
            };
            writeStorage(memoryCache);
        }

        const loadTab = async (requestedTab, options = { pushState: true }) => {
            const tab = normalizeTab(requestedTab);
            const shouldPushState = options.pushState !== false;

            setActiveTabVisuals(tab);

            if (memoryCache[tab]?.html) {
                contentEl.innerHTML = memoryCache[tab].html;
                if (pageTitleEl) {
                    pageTitleEl.textContent =
                        memoryCache[tab].title ?? getTitleFromTab(tab);
                }

                if (shouldPushState) {
                    const nextUrl = `${dashboardBaseUrl}?tab=${encodeURIComponent(tab)}`;
                    window.history.pushState({ tab }, "", nextUrl);
                }
                return;
            }

            setLoading(true);
            try {
                const response = await fetch(
                    endpointTemplate.replace(
                        "__TAB__",
                        encodeURIComponent(tab),
                    ),
                    {
                        headers: {
                            Accept: "text/html",
                            Authorization: `Bearer ${token}`,
                            "X-Requested-With": "XMLHttpRequest",
                        },
                    },
                );

                if (response.status === 401) {
                    localStorage.removeItem(TOKEN_KEY);
                    sessionStorage.removeItem(DASHBOARD_CACHE_KEY);
                    window.location.assign("/login");
                    return;
                }

                if (!response.ok) {
                    throw new Error(
                        `Unable to load tab content (${response.status}).`,
                    );
                }

                const html = await response.text();
                const title = getTitleFromTab(tab);

                contentEl.innerHTML = html;
                if (pageTitleEl) {
                    pageTitleEl.textContent = title;
                }

                memoryCache = {
                    ...memoryCache,
                    [tab]: { html, title },
                };
                writeStorage(memoryCache);

                if (shouldPushState) {
                    const nextUrl = `${dashboardBaseUrl}?tab=${encodeURIComponent(tab)}`;
                    window.history.pushState({ tab }, "", nextUrl);
                }
            } catch (error) {
                if (contentEl) {
                    contentEl.innerHTML =
                        '<div class="rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-700">Failed to load this section. Please try again.</div>';
                }
                if (pageTitleEl) {
                    pageTitleEl.textContent = "Dashboard";
                }

                if (output) {
                    output.textContent = JSON.stringify(
                        {
                            endpoint: "dashboard.tab.load",
                            status: "error",
                            message:
                                error instanceof Error
                                    ? error.message
                                    : "Unknown tab loading error.",
                        },
                        null,
                        2,
                    );
                }
            } finally {
                setLoading(false);
            }
        };

        document.addEventListener("click", (event) => {
            const target =
                event.target instanceof Element
                    ? event.target.closest("[data-dashboard-tab]")
                    : null;
            if (!(target instanceof HTMLAnchorElement)) {
                return;
            }

            if (
                event.metaKey ||
                event.ctrlKey ||
                event.shiftKey ||
                event.altKey ||
                target.target === "_blank"
            ) {
                return;
            }

            event.preventDefault();
            const tab = normalizeTab(target.dataset.dashboardTab);
            loadTab(tab);
        });

        window.addEventListener("popstate", () => {
            const urlTab = normalizeTab(
                new URLSearchParams(window.location.search).get("tab") ??
                    "dashboard",
            );
            loadTab(urlTab, { pushState: false });
        });

        setActiveTabVisuals(initialTab);
    };

    callProtected("/auth/session");
    initDynamicDashboardTabs();

    sessionBtn?.addEventListener("click", () => {
        callProtected("/auth/session");
    });

    adminBtn?.addEventListener("click", () => {
        callProtected("/admin/ping");
    });

    logoutBtn?.addEventListener("click", async () => {
        localStorage.removeItem(TOKEN_KEY);
        sessionStorage.removeItem(DASHBOARD_CACHE_KEY);
        await fetch("/auth/logout", {
            headers: {
                Accept: "application/json",
            },
        });
        window.location.assign("/login");
    });
}

initLoginFlow();
initDashboardFlow();
