import { createClient } from "@supabase/supabase-js";

const TOKEN_KEY = "aics_supabase_access_token";
const OTP_SESSION_KEY = "aics_otp_session_id";
const LEGACY_DASHBOARD_CACHE_KEY = "aics_dashboard_tab_cache_v1";
const DASHBOARD_CACHE_KEY = "aics_dashboard_tab_cache_v2";
const DASHBOARD_ALLOWED_TABS = new Set(["dashboard", "audit-log"]);

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
    const otpSection = document.getElementById("otp-section");
    const authCard = document.getElementById("auth-card");
    const stepTitle = document.getElementById("login-step-title");
    const stepSubtitle = document.getElementById("login-step-subtitle");
    const otpInput = document.getElementById("otp-code");
    const otpDigitInputs = Array.from(
        document.querySelectorAll("[data-otp-digit]"),
    );
    const otpVerifyBtn = document.getElementById("otp-verify-btn");
    const otpResendBtn = document.getElementById("otp-resend-btn");
    const otpBackBtn = document.getElementById("otp-back-btn");
    const otpHelpText = document.getElementById("otp-help-text");
    const emailInput = document.getElementById("email");
    const passwordInput = document.getElementById("password");
    const passwordSubmitBtn = document.getElementById("password-submit-btn");

    if (!form || !appConfig?.url || !appConfig?.anonKey) {
        return;
    }

    const supabase = createClient(appConfig.url, appConfig.anonKey, {
        auth: {
            persistSession: false,
        },
    });

    const csrfToken =
        document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content") ?? "";

    const getCurrentToken = () => localStorage.getItem(TOKEN_KEY) ?? "";
    const getOtpSessionId = () => sessionStorage.getItem(OTP_SESSION_KEY) ?? "";
    let otpRequestPending = false;

    const setPasswordFormEnabled = (enabled) => {
        if (emailInput) emailInput.readOnly = !enabled;
        if (passwordInput) passwordInput.readOnly = !enabled;
        if (passwordSubmitBtn) passwordSubmitBtn.disabled = !enabled;
    };

    const syncOtpHiddenInput = () => {
        if (!otpInput) {
            return "";
        }

        const code = otpDigitInputs.map((input) => input.value.trim()).join("");
        otpInput.value = code;
        return code;
    };

    const clearOtpInputs = () => {
        otpDigitInputs.forEach((input) => {
            input.value = "";
        });
        syncOtpHiddenInput();
    };

    const setButtonEnabled = (button, enabled) => {
        if (!(button instanceof HTMLButtonElement)) {
            return;
        }

        button.disabled = !enabled;
    };

    const setButtonLoading = (button, isLoading) => {
        if (!(button instanceof HTMLButtonElement)) {
            return;
        }

        button.dataset.loading = isLoading ? "true" : "false";
        button.setAttribute("aria-busy", isLoading ? "true" : "false");

        const spinner = button.querySelector("[data-btn-spinner]");
        if (spinner instanceof HTMLElement) {
            spinner.classList.toggle("hidden", !isLoading);
        }

        const label = button.querySelector("[data-btn-label]");
        if (label instanceof HTMLElement) {
            if (!button.dataset.defaultLabel) {
                button.dataset.defaultLabel = label.textContent?.trim() ?? "";
            }

            const loadingText =
                button.dataset.loadingText?.trim() || "Loading...";

            label.textContent = isLoading
                ? loadingText
                : button.dataset.defaultLabel;
        }
    };

    const syncOtpActionAvailability = () => {
        const hasOtpSession = Boolean(getOtpSessionId());
        setButtonEnabled(otpVerifyBtn, hasOtpSession && !otpRequestPending);
        setButtonEnabled(otpResendBtn, !otpRequestPending);

        if (hasOtpSession && !otpRequestPending) {
            setButtonLoading(otpVerifyBtn, false);
        }

        if (!otpRequestPending) {
            setButtonLoading(otpResendBtn, false);
        }
    };

    const updateOtpHelpText = (maskedEmail = "") => {
        if (!otpHelpText) {
            return;
        }

        otpHelpText.textContent = maskedEmail
            ? `Enter the 6-digit OTP sent to ${maskedEmail}.`
            : "Enter the 6-digit OTP sent to your email address.";
    };

    const focusOtpInput = (index) => {
        const safeIndex = Math.max(
            0,
            Math.min(index, otpDigitInputs.length - 1),
        );
        otpDigitInputs[safeIndex]?.focus();
        otpDigitInputs[safeIndex]?.select();
    };

    const setStepContent = (step) => {
        if (!form || !otpSection) {
            return;
        }

        if (step === "otp") {
            form.classList.add("hidden");
            otpSection.classList.remove("hidden");
            if (authCard instanceof HTMLElement) {
                authCard.style.maxWidth = "40rem";
            }
            if (stepTitle) stepTitle.textContent = "Enter verification code";
            if (stepSubtitle)
                stepSubtitle.textContent =
                    "Complete sign-in using the 6-digit code sent to your email.";
        } else {
            form.classList.remove("hidden");
            otpSection.classList.add("hidden");
            if (authCard instanceof HTMLElement) {
                authCard.style.maxWidth = "30rem";
            }
            if (stepTitle) stepTitle.textContent = "Welcome to AICS Program";
            if (stepSubtitle)
                stepSubtitle.textContent =
                    "Sign in with your staff account to access and manage applications.";
            clearOtpInputs();
        }
    };

    const showOtpSection = (maskedEmail = "") => {
        if (!otpSection) {
            return;
        }

        setStepContent("otp");
        updateOtpHelpText(maskedEmail);
        setPasswordFormEnabled(false);
        clearOtpInputs();
        focusOtpInput(0);
        syncOtpActionAvailability();
    };

    otpDigitInputs.forEach((input, index) => {
        input.addEventListener("input", (event) => {
            const target = event.currentTarget;
            if (!(target instanceof HTMLInputElement)) {
                return;
            }

            const digitsOnly = target.value.replace(/\D/g, "");
            target.value = digitsOnly.slice(-1);

            syncOtpHiddenInput();

            if (target.value && index < otpDigitInputs.length - 1) {
                focusOtpInput(index + 1);
            }
        });

        input.addEventListener("keydown", (event) => {
            if (event.key === "Backspace") {
                const target = event.currentTarget;
                if (!(target instanceof HTMLInputElement)) {
                    return;
                }

                if (target.value === "" && index > 0) {
                    otpDigitInputs[index - 1].value = "";
                    syncOtpHiddenInput();
                    focusOtpInput(index - 1);
                    event.preventDefault();
                }
            }

            if (event.key === "ArrowLeft" && index > 0) {
                focusOtpInput(index - 1);
                event.preventDefault();
            }

            if (
                event.key === "ArrowRight" &&
                index < otpDigitInputs.length - 1
            ) {
                focusOtpInput(index + 1);
                event.preventDefault();
            }
        });

        input.addEventListener("paste", (event) => {
            const clipboard = event.clipboardData?.getData("text") ?? "";
            const digits = clipboard
                .replace(/\D/g, "")
                .slice(0, otpDigitInputs.length);

            if (!digits) {
                return;
            }

            event.preventDefault();

            otpDigitInputs.forEach((otpBox, otpIndex) => {
                otpBox.value = digits[otpIndex] ?? "";
            });
            syncOtpHiddenInput();

            const nextIndex = Math.min(
                digits.length,
                otpDigitInputs.length - 1,
            );
            focusOtpInput(nextIndex);
        });
    });

    const reportLoginAttempt = async (outcome, reason = "") => {
        const email = emailInput?.value?.trim() ?? "";
        if (!email) {
            return null;
        }

        try {
            const response = await fetch("/auth/login-attempt", {
                method: "POST",
                headers: {
                    Accept: "application/json",
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                    "X-Requested-With": "XMLHttpRequest",
                },
                body: JSON.stringify({
                    email,
                    outcome,
                    reason,
                }),
            });

            const payload = await response.json();

            return {
                ok: response.ok,
                status: response.status,
                payload,
            };
        } catch {
            // no-op: telemetry should not block auth UX
            return null;
        }
    };

    const checkLoginCooldown = async (email) => {
        const response = await fetch("/auth/login-cooldown-check", {
            method: "POST",
            headers: {
                Accept: "application/json",
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken,
                "X-Requested-With": "XMLHttpRequest",
            },
            body: JSON.stringify({
                email,
            }),
        });

        const payload = await response.json();

        return {
            ok: response.ok,
            status: response.status,
            payload,
        };
    };

    const requestOtp = async (token, isResend = false) => {
        const response = await fetch("/auth/otp/request", {
            method: "POST",
            headers: {
                Accept: "application/json",
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken,
                "X-Requested-With": "XMLHttpRequest",
                Authorization: `Bearer ${token}`,
            },
            body: JSON.stringify({
                is_resend: isResend,
            }),
        });

        const payload = await response.json();
        if (!response.ok) {
            throw new Error(payload.message ?? "Failed to request OTP.");
        }

        return payload;
    };

    const verifyOtp = async (token, otpSessionId, otpCode) => {
        const response = await fetch("/auth/otp/verify", {
            method: "POST",
            headers: {
                Accept: "application/json",
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken,
                "X-Requested-With": "XMLHttpRequest",
                Authorization: `Bearer ${token}`,
            },
            body: JSON.stringify({
                otp_session_id: otpSessionId,
                otp_code: otpCode,
            }),
        });

        const payload = await response.json();
        if (!response.ok) {
            throw new Error(payload.message ?? "OTP verification failed.");
        }

        return payload;
    };

    form.addEventListener("submit", async (event) => {
        event.preventDefault();

        const formData = new FormData(form);
        const email = String(formData.get("email") ?? "");
        const password = String(formData.get("password") ?? "");
        let passwordAuthenticated = false;

        try {
            setButtonLoading(passwordSubmitBtn, true);
            setStatus("Signing in...");

            const cooldownState = await checkLoginCooldown(email);
            if (!cooldownState.ok && cooldownState.status === 429) {
                throw new Error(
                    String(
                        cooldownState.payload?.message ??
                            "Too many failed attempts. Please try again later.",
                    ),
                );
            }

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
            passwordAuthenticated = true;

            sessionStorage.removeItem(OTP_SESSION_KEY);
            otpRequestPending = true;
            showOtpSection();
            setStatus("Password accepted. Sending your OTP now...");

            const otpPayload = await requestOtp(token);
            otpRequestPending = false;
            sessionStorage.setItem(
                OTP_SESSION_KEY,
                String(otpPayload.otp_session_id ?? ""),
            );
            syncOtpActionAvailability();

            updateOtpHelpText(String(otpPayload.masked_email ?? ""));
            setStatus(
                String(
                    otpPayload.message ??
                        "Password accepted. Enter the 6-digit OTP sent to your email.",
                ),
            );
        } catch (err) {
            otpRequestPending = false;
            syncOtpActionAvailability();

            if (!passwordAuthenticated) {
                const attemptResult = await reportLoginAttempt(
                    "failed",
                    err instanceof Error
                        ? err.message
                        : "Unexpected authentication error.",
                );

                if (attemptResult?.status === 429) {
                    setStatus(
                        String(
                            attemptResult.payload?.message ??
                                "Too many failed attempts. Please try again in 15 minutes.",
                        ),
                        true,
                    );
                    return;
                }
            }

            setStatus(
                err instanceof Error
                    ? err.message
                    : "Unexpected authentication error.",
                true,
            );
        } finally {
            setButtonLoading(passwordSubmitBtn, false);
        }
    });

    otpVerifyBtn?.addEventListener("click", async () => {
        const token = getCurrentToken();
        const otpSessionId = getOtpSessionId();
        const otpCode = syncOtpHiddenInput();

        if (otpRequestPending) {
            setStatus("OTP is still being sent. Please wait a moment.", true);
            return;
        }

        if (!token) {
            await reportLoginAttempt(
                "session_expired",
                "Token missing during OTP verification.",
            );
            setStatus("Session expired. Please sign in again.", true);
            setPasswordFormEnabled(true);
            otpSection?.classList.add("hidden");
            return;
        }

        if (!otpSessionId) {
            setStatus("OTP session missing. Please request a new OTP.", true);
            return;
        }

        if (!/^\d{6}$/.test(otpCode)) {
            setStatus("Please enter a valid 6-digit OTP code.", true);
            return;
        }

        try {
            setButtonLoading(otpVerifyBtn, true);
            setStatus("Verifying OTP...");
            await verifyOtp(token, otpSessionId, otpCode);
            sessionStorage.removeItem(OTP_SESSION_KEY);
            syncOtpActionAvailability();

            await validateBackendSession(token);
            setStatus("Sign in successful. Redirecting to dashboard...");
            window.location.assign("/dashboard");
        } catch (err) {
            setStatus(
                err instanceof Error ? err.message : "OTP verification failed.",
                true,
            );
        } finally {
            setButtonLoading(otpVerifyBtn, false);
        }
    });

    otpResendBtn?.addEventListener("click", async () => {
        const token = getCurrentToken();
        if (!token) {
            await reportLoginAttempt(
                "session_expired",
                "Token missing during OTP resend.",
            );
            setStatus("Session expired. Please sign in again.", true);
            return;
        }

        if (otpRequestPending) {
            setStatus("OTP request in progress. Please wait.", true);
            return;
        }

        try {
            otpRequestPending = true;
            syncOtpActionAvailability();
            setButtonLoading(otpResendBtn, true);
            setStatus("Requesting a new OTP...");
            const otpPayload = await requestOtp(token, true);
            otpRequestPending = false;
            sessionStorage.setItem(
                OTP_SESSION_KEY,
                String(otpPayload.otp_session_id ?? ""),
            );
            syncOtpActionAvailability();
            updateOtpHelpText(String(otpPayload.masked_email ?? ""));
            setStatus(String(otpPayload.message ?? "A new OTP has been sent."));
        } catch (err) {
            otpRequestPending = false;
            syncOtpActionAvailability();
            setStatus(
                err instanceof Error ? err.message : "Unable to resend OTP.",
                true,
            );
        } finally {
            setButtonLoading(otpResendBtn, false);
        }
    });

    otpBackBtn?.addEventListener("click", () => {
        otpRequestPending = false;
        setPasswordFormEnabled(true);
        sessionStorage.removeItem(OTP_SESSION_KEY);
        syncOtpActionAvailability();
        setStepContent("password");
        setStatus("You can sign in again to request a new OTP.");
    });

    if (getCurrentToken() && getOtpSessionId()) {
        showOtpSection();
        setStatus("Enter the OTP code to continue.");
    } else {
        syncOtpActionAvailability();
        setStepContent("password");
    }
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
            sessionStorage.removeItem(OTP_SESSION_KEY);
            window.location.assign("/login");
            return;
        }

        if (response.status === 403) {
            const message = String(payload?.message ?? "");
            if (message.toLowerCase().includes("otp verification required")) {
                localStorage.removeItem(TOKEN_KEY);
                sessionStorage.removeItem(OTP_SESSION_KEY);
                window.location.assign("/login");
                return;
            }
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

        sessionStorage.removeItem(LEGACY_DASHBOARD_CACHE_KEY);

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
        const emitDashboardFragmentUpdated = () => {
            if (!(contentEl instanceof HTMLElement)) {
                return;
            }

            document.dispatchEvent(
                new CustomEvent("aics:dashboard-fragment-updated", {
                    detail: {
                        container: contentEl,
                    },
                }),
            );
        };
        let liveSearchDebounceTimer = null;

        const captureLiveSearchState = () => {
            const activeElement = document.activeElement;
            if (!(activeElement instanceof HTMLInputElement)) {
                return null;
            }

            if (!activeElement.matches("[data-live-search-input]")) {
                return null;
            }

            return {
                id: activeElement.id,
                name: activeElement.name,
                value: activeElement.value,
                selectionStart: activeElement.selectionStart,
                selectionEnd: activeElement.selectionEnd,
            };
        };

        const restoreLiveSearchState = (state) => {
            if (!state) {
                return;
            }

            const selector = state.id
                ? `#${CSS.escape(state.id)}`
                : `input[data-live-search-input][name="${CSS.escape(state.name)}"]`;

            const nextInput = document.querySelector(selector);
            if (!(nextInput instanceof HTMLInputElement)) {
                return;
            }

            nextInput.focus({ preventScroll: true });
            nextInput.value = state.value;

            if (
                Number.isInteger(state.selectionStart) &&
                Number.isInteger(state.selectionEnd)
            ) {
                nextInput.setSelectionRange(
                    state.selectionStart,
                    state.selectionEnd,
                );
            }
        };

        const buildFragmentUrlFromForm = (form) => {
            const action =
                form.getAttribute("action") ||
                endpointTemplate.replace(
                    "__TAB__",
                    encodeURIComponent(activeTab),
                );
            const formData = new FormData(form);
            const search = new URLSearchParams();

            formData.forEach((value, key) => {
                const normalized =
                    typeof value === "string" ? value.trim() : "";
                if (normalized !== "") {
                    search.set(key, normalized);
                }
            });

            return search.toString()
                ? `${action}?${search.toString()}`
                : action;
        };

        let activeTab = "dashboard";

        const syncActiveTabCache = (tabKey, html) => {
            const tab = normalizeTab(tabKey);
            memoryCache = {
                ...memoryCache,
                [tab]: {
                    html,
                    title: getTitleFromTab(tab),
                },
            };
            writeStorage(memoryCache);
        };

        const loadContentUrl = async (url) => {
            const liveSearchState = captureLiveSearchState();
            setLoading(true);

            try {
                const response = await fetch(url, {
                    headers: {
                        Accept: "text/html",
                        Authorization: `Bearer ${token}`,
                        "X-Requested-With": "XMLHttpRequest",
                    },
                });

                if (response.status === 401) {
                    localStorage.removeItem(TOKEN_KEY);
                    sessionStorage.removeItem(DASHBOARD_CACHE_KEY);
                    window.location.assign("/login");
                    return;
                }

                if (!response.ok) {
                    throw new Error(
                        `Unable to load content (${response.status}).`,
                    );
                }

                const html = await response.text();
                contentEl.innerHTML = html;
                emitDashboardFragmentUpdated();
                restoreLiveSearchState(liveSearchState);
                syncActiveTabCache(activeTab, html);
            } catch (error) {
                contentEl.innerHTML =
                    '<div class="rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-700">Failed to load this section. Please try again.</div>';

                if (output) {
                    output.textContent = JSON.stringify(
                        {
                            endpoint: "dashboard.content.pagination",
                            status: "error",
                            message:
                                error instanceof Error
                                    ? error.message
                                    : "Unknown content loading error.",
                        },
                        null,
                        2,
                    );
                }
            } finally {
                setLoading(false);
            }
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
                "audit-log": "Audit Log",
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

            activeTab = tab;
            contentEl.dataset.activeTab = tab;

            setActiveTabVisuals(tab);

            if (memoryCache[tab]?.html) {
                contentEl.innerHTML = memoryCache[tab].html;
                emitDashboardFragmentUpdated();
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
                emitDashboardFragmentUpdated();
                if (pageTitleEl) {
                    pageTitleEl.textContent = title;
                }

                syncActiveTabCache(tab, html);

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

        contentEl.addEventListener("click", (event) => {
            const target =
                event.target instanceof Element
                    ? event.target.closest(
                          "a[data-dashboard-pagination], a[data-audit-pagination]",
                      )
                    : null;

            if (!(target instanceof HTMLAnchorElement)) {
                return;
            }

            event.preventDefault();
            loadContentUrl(target.href);
        });

        contentEl.addEventListener("submit", (event) => {
            const target = event.target;
            if (!(target instanceof HTMLFormElement)) {
                return;
            }

            if (!target.matches("form[data-dashboard-fragment-form]")) {
                return;
            }

            event.preventDefault();

            if (liveSearchDebounceTimer) {
                window.clearTimeout(liveSearchDebounceTimer);
                liveSearchDebounceTimer = null;
            }

            loadContentUrl(buildFragmentUrlFromForm(target));
        });

        contentEl.addEventListener("input", (event) => {
            const target = event.target;
            if (!(target instanceof HTMLInputElement)) {
                return;
            }

            if (!target.matches("[data-live-search-input]")) {
                return;
            }

            const form = target.closest("form[data-dashboard-fragment-form]");
            if (!(form instanceof HTMLFormElement)) {
                return;
            }

            if (liveSearchDebounceTimer) {
                window.clearTimeout(liveSearchDebounceTimer);
            }

            liveSearchDebounceTimer = window.setTimeout(() => {
                loadContentUrl(buildFragmentUrlFromForm(form));
            }, 300);
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
        const activeToken = localStorage.getItem(TOKEN_KEY);

        localStorage.removeItem(TOKEN_KEY);
        sessionStorage.removeItem(OTP_SESSION_KEY);
        sessionStorage.removeItem(DASHBOARD_CACHE_KEY);
        await fetch("/auth/logout", {
            headers: {
                Accept: "application/json",
                ...(activeToken
                    ? { Authorization: `Bearer ${activeToken}` }
                    : {}),
            },
        });
        window.location.assign("/login");
    });
}

initLoginFlow();
initDashboardFlow();
