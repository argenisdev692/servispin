function data() {
    function getThemeFromLocalStorage() {
        const theme = window.localStorage.getItem("admin-theme");
        if (theme === "dark") {
            return true;
        }
        if (theme === "light") {
            return false;
        }

        // Migrar preferencia antigua (seguía prefers-color-scheme → todo oscuro en Edge)
        const legacy = window.localStorage.getItem("dark");
        if (legacy !== null) {
            const isDark = JSON.parse(legacy);
            window.localStorage.setItem("admin-theme", isDark ? "dark" : "light");
            window.localStorage.removeItem("dark");
            return isDark;
        }

        return false;
    }

    function setThemeToLocalStorage(value) {
        window.localStorage.setItem("admin-theme", value ? "dark" : "light");
    }

    return {
        dark: getThemeFromLocalStorage(),
        toggleTheme() {
            this.dark = !this.dark;
            setThemeToLocalStorage(this.dark);
        },
        isSideMenuOpen: false,
        toggleSideMenu() {
            this.isSideMenuOpen = !this.isSideMenuOpen;
        },
        closeSideMenu() {
            this.isSideMenuOpen = false;
        },
        isNotificationsMenuOpen: false,
        toggleNotificationsMenu() {
            this.isNotificationsMenuOpen = !this.isNotificationsMenuOpen;
        },
        closeNotificationsMenu() {
            this.isNotificationsMenuOpen = false;
        },
        isProfileMenuOpen: false,
        toggleProfileMenu() {
            this.isProfileMenuOpen = !this.isProfileMenuOpen;
        },
        closeProfileMenu() {
            this.isProfileMenuOpen = false;
        },
        isPagesMenuOpen: false,
        togglePagesMenu() {
            this.isPagesMenuOpen = !this.isPagesMenuOpen;
        },
        isPagesMenuOpen2: false,
        togglePagesMenu2() {
            this.isPagesMenuOpen2 = !this.isPagesMenuOpen2;
        },
        isPagesMenuOpen3: false,
        togglePagesMenu3() {
            this.isPagesMenuOpen3 = !this.isPagesMenuOpen3;
        },
        isPagesMenuOpen4: false,
        togglePagesMenu4() {
            this.isPagesMenuOpen4 = !this.isPagesMenuOpen4;
        },
        isPagesMenuOpen5: false,
        togglePagesMenu5() {
            this.isPagesMenuOpen5 = !this.isPagesMenuOpen5;
        },
        // Modal
        isModalOpen: false,
        trapCleanup: null,
        openModal() {
            this.isModalOpen = true;
            this.trapCleanup = focusTrap(document.querySelector("#modal"));
        },
        closeModal() {
            this.isModalOpen = false;
            this.trapCleanup();
        },
    };
}
