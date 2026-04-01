const EMAIL_REGEX = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

function sanitizeName(value) {
    return String(value ?? "")
        .replace(/[^A-Za-z\s'\-]/g, "")
        .replace(/\s{2,}/g, " ")
        .trim();
}

function sanitizeEmail(value) {
    return String(value ?? "")
        .trim()
        .toLowerCase()
        .replace(/\s+/g, "");
}

function sanitizePassword(value) {
    return String(value ?? "").trim();
}

function setError(form, fieldName, message = "") {
    const input = form.querySelector(`[name="${fieldName}"]`);
    const errorEl = form.querySelector(`[data-error-for="${fieldName}"]`);

    if (input instanceof HTMLElement) {
        input.classList.toggle("border-red-400", Boolean(message));
        input.classList.toggle("focus:border-red-500", Boolean(message));
    }

    if (!(errorEl instanceof HTMLElement)) {
        return;
    }

    errorEl.textContent = message;
    errorEl.classList.toggle("hidden", !message);
}

function sanitizeForm(form) {
    const firstName = form.querySelector('[name="first_name"]');
    const lastName = form.querySelector('[name="last_name"]');
    const email = form.querySelector('[name="email"]');
    const password = form.querySelector('[name="password"]');

    if (firstName instanceof HTMLInputElement) {
        firstName.value = sanitizeName(firstName.value);
    }
    if (lastName instanceof HTMLInputElement) {
        lastName.value = sanitizeName(lastName.value);
    }
    if (email instanceof HTMLInputElement) {
        email.value = sanitizeEmail(email.value);
    }
    if (password instanceof HTMLInputElement) {
        password.value = sanitizePassword(password.value);
    }
}

function validateForm(form) {
    sanitizeForm(form);

    let isValid = true;

    const firstName = form.querySelector('[name="first_name"]');
    const lastName = form.querySelector('[name="last_name"]');
    const email = form.querySelector('[name="email"]');
    const password = form.querySelector('[name="password"]');
    const role = form.querySelector('[name="role"]');

    const firstNameValue =
        firstName instanceof HTMLInputElement ? firstName.value : "";
    const lastNameValue =
        lastName instanceof HTMLInputElement ? lastName.value : "";
    const emailValue = email instanceof HTMLInputElement ? email.value : "";
    const passwordValue =
        password instanceof HTMLInputElement ? password.value : "";
    const roleValue = role instanceof HTMLSelectElement ? role.value : "";

    if (firstNameValue.length < 2) {
        setError(
            form,
            "first_name",
            "First name must be at least 2 characters.",
        );
        isValid = false;
    } else {
        setError(form, "first_name", "");
    }

    if (lastNameValue.length < 2) {
        setError(form, "last_name", "Last name must be at least 2 characters.");
        isValid = false;
    } else {
        setError(form, "last_name", "");
    }

    if (!EMAIL_REGEX.test(emailValue)) {
        setError(form, "email", "Enter a valid email address.");
        isValid = false;
    } else {
        setError(form, "email", "");
    }

    if (passwordValue.length < 6) {
        setError(form, "password", "Password must be at least 6 characters.");
        isValid = false;
    } else {
        setError(form, "password", "");
    }

    if (!roleValue.trim()) {
        setError(form, "role", "Please select a role.");
        isValid = false;
    } else {
        setError(form, "role", "");
    }

    return isValid;
}

function bindAddUserFormBehavior() {
    if (document.__aicsAddUserValidationBound === true) {
        return;
    }

    document.__aicsAddUserValidationBound = true;

    document.addEventListener("click", (event) => {
        const target = event.target;
        if (!(target instanceof HTMLElement)) {
            return;
        }

        const toggle = target.closest("[data-toggle-password]");
        if (!(toggle instanceof HTMLButtonElement)) {
            return;
        }

        const inputId = toggle.getAttribute("data-target") ?? "";
        const input = document.getElementById(inputId);
        if (!(input instanceof HTMLInputElement)) {
            return;
        }

        const shouldShow = input.type === "password";
        input.type = shouldShow ? "text" : "password";
        toggle.textContent = shouldShow ? "Hide" : "Show";
        toggle.setAttribute("aria-pressed", shouldShow ? "true" : "false");
        toggle.setAttribute(
            "aria-label",
            shouldShow ? "Hide password" : "Show password",
        );
    });

    document.addEventListener("input", (event) => {
        const target = event.target;
        if (!(target instanceof HTMLInputElement)) {
            return;
        }

        const form = target.closest("form[data-add-user-form]");
        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        const mode = target.getAttribute("data-sanitize");
        if (mode === "name") {
            target.value = sanitizeName(target.value);
        } else if (mode === "email") {
            target.value = sanitizeEmail(target.value);
        } else if (mode === "password") {
            target.value = sanitizePassword(target.value);
        }
    });

    document.addEventListener(
        "submit",
        (event) => {
            const form = event.target;
            if (
                !(form instanceof HTMLFormElement) ||
                !form.matches("form[data-add-user-form]")
            ) {
                return;
            }

            if (!validateForm(form)) {
                event.preventDefault();
            }
        },
        true,
    );
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", bindAddUserFormBehavior);
} else {
    bindAddUserFormBehavior();
}

export { sanitizeName, sanitizeEmail, sanitizePassword, validateForm };
