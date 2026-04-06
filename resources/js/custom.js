document.addEventListener("DOMContentLoaded", () => {
    // Clock Display
    const clockDisplay = document.getElementById("clockDisplay");
    function updateClock() {
        const now = new Date();
        const options = {
            weekday: "short",
            year: "numeric",
            month: "short",
            day: "numeric",
            hour: "2-digit",
            minute: "2-digit",
            second: "2-digit",
            hour12: true,
        };
        if (clockDisplay) {
            clockDisplay.textContent = now.toLocaleDateString("en-US", options);
        }
    }
    updateClock();
    setInterval(updateClock, 1000);

// Sidebar Toggle
const menuBtn = document.getElementById("menuBtn");
const sidebar = document.getElementById("sidebar");
const sidebarOverlay = document.getElementById("sidebarOverlay");
const closeSidebar = document.getElementById("closeSidebar");

function toggleSidebar(open = null) {
    const isOpen = !sidebar.classList.contains("-translate-x-full");
    const shouldOpen = open ?? !isOpen;

    if (shouldOpen) {
        sidebar.classList.remove("-translate-x-full");
        sidebarOverlay.classList.remove("opacity-0", "pointer-events-none");
    } else {
        sidebar.classList.add("-translate-x-full");
        sidebarOverlay.classList.add("opacity-0", "pointer-events-none");
    }
}

// Click events
menuBtn?.addEventListener("click", () => toggleSidebar(true));
closeSidebar?.addEventListener("click", () => toggleSidebar(false));
sidebarOverlay?.addEventListener("click", () => toggleSidebar(false));

// Spacebar toggle
document.addEventListener("keydown", (e) => {
    if (e.code === "Space" && !sidebar.classList.contains("-translate-x-full")) {
        e.preventDefault(); 
        toggleSidebar(false);
    }
});

// Swipe detection
let touchStartX = 0;
let touchEndX = 0;

document.addEventListener("touchstart", (e) => {
    touchStartX = e.changedTouches[0].screenX;
});

document.addEventListener("touchend", (e) => {
    touchEndX = e.changedTouches[0].screenX;
    handleGesture();
});

function handleGesture() {
    const deltaX = touchEndX - touchStartX;

    // Swipe right to open (from left edge)
    if (deltaX > 50 && sidebar.classList.contains("-translate-x-full") && touchStartX < 50) {
        toggleSidebar(true);
    }
    // Swipe left to close
    if (deltaX < -50 && !sidebar.classList.contains("-translate-x-full")) {
        toggleSidebar(false);
    }
}



    //calender event js

    // function calendarComponent() {
    //     return {
    //         today: new Date(),
    //         selectedDate: null,
    //         currentMonth: new Date().getMonth(),
    //         currentYear: new Date().getFullYear(),
    //         events: [
    //             {
    //                 title: "Team Meeting",
    //                 date: "2025-07-25",
    //                 time: "2:00 PM",
    //             },
    //             {
    //                 title: "Quarterly Review",
    //                 date: "2025-07-26",
    //                 time: "10:00 AM",
    //             },
    //             {
    //                 title: "New Hire Orientation",
    //                 date: "2025-07-28",
    //                 time: "9:00 AM",
    //             },
    //         ],
    //         calendarDays: [],
    //         monthNames: [
    //             "January",
    //             "February",
    //             "March",
    //             "April",
    //             "May",
    //             "June",
    //             "July",
    //             "August",
    //             "September",
    //             "October",
    //             "November",
    //             "December",
    //         ],

    //         init() {
    //             this.selectedDate = this.formatDateISO(this.today);
    //             this.generateCalendar();
    //         },

    //         formatDateISO(date) {
    //             return date.toISOString().split("T")[0];
    //         },

    //         generateCalendar() {
    //             let firstDay = new Date(this.currentYear, this.currentMonth, 1);
    //             let startDay = firstDay.getDay();
    //             let totalDays = new Date(
    //                 this.currentYear,
    //                 this.currentMonth + 1,
    //                 0
    //             ).getDate();

    //             this.calendarDays = [];

    //             for (let i = 0; i < startDay; i++) {
    //                 this.calendarDays.push(
    //                     new Date(
    //                         this.currentYear,
    //                         this.currentMonth,
    //                         i - startDay + 1
    //                     )
    //                 );
    //             }

    //             for (let i = 1; i <= totalDays; i++) {
    //                 this.calendarDays.push(
    //                     new Date(this.currentYear, this.currentMonth, i)
    //                 );
    //             }

    //             while (this.calendarDays.length < 42) {
    //                 this.calendarDays.push(
    //                     new Date(
    //                         this.currentYear,
    //                         this.currentMonth + 1,
    //                         this.calendarDays.length - totalDays - startDay + 1
    //                     )
    //                 );
    //             }
    //         },

    //         prevMonth() {
    //             if (--this.currentMonth < 0) {
    //                 this.currentMonth = 11;
    //                 this.currentYear--;
    //             }
    //             this.generateCalendar();
    //         },

    //         nextMonth() {
    //             if (++this.currentMonth > 11) {
    //                 this.currentMonth = 0;
    //                 this.currentYear++;
    //             }
    //             this.generateCalendar();
    //         },

    //         selectDate(date) {
    //             this.selectedDate = this.formatDateISO(date);
    //         },

    //         isToday(date) {
    //             return this.formatDateISO(date) === this.formatDateISO(this.today);
    //         },

    //         isSelected(date) {
    //             return this.formatDateISO(date) === this.selectedDate;
    //         },

    //         hasEvent(date) {
    //             return this.events.some((e) => e.date === this.formatDateISO(date));
    //         },

    //         get filteredEvents() {
    //             return this.events.filter((e) => e.date === this.selectedDate);
    //         },

    //         get upcomingEvents() {
    //             const todayISO = this.formatDateISO(this.today);
    //             return this.events.filter((e) => e.date > todayISO);
    //         },

    //         formatDate(dateStr) {
    //             const d = new Date(dateStr);
    //             return d.toDateString();
    //         },

    //         get selectedDateLabel() {
    //             const d = new Date(this.selectedDate);
    //             return d.toDateString();
    //         },
    //     };
    // }






    //notification sound


    const toggleButton = document.getElementById('toggleSound');
    const toggleThumb = document.getElementById('toggleThumb');

    // Load initial state
    const soundSetting = localStorage.getItem('alertSound');
    let isOn = soundSetting !== 'off';

    if (!isOn) {
        toggleButton.classList.remove('bg-green-500');
        toggleButton.classList.add('bg-gray-300');
        toggleThumb.classList.remove('translate-x-5');
        toggleThumb.classList.add('translate-x-0');
        toggleButton.setAttribute('aria-checked', 'false');
    }

    // Toggle click
    toggleButton.addEventListener('click', () => {
        isOn = !isOn;

        if (isOn) {
            toggleButton.classList.remove('bg-gray-300');
            toggleButton.classList.add('bg-green-500');
            toggleThumb.classList.remove('translate-x-0');
            toggleThumb.classList.add('translate-x-5');
            localStorage.setItem('alertSound', 'on');
            toggleButton.setAttribute('aria-checked', 'true');
        } else {
            toggleButton.classList.remove('bg-green-500');
            toggleButton.classList.add('bg-gray-300');
            toggleThumb.classList.remove('translate-x-5');
            toggleThumb.classList.add('translate-x-0');
            localStorage.setItem('alertSound', 'off');
            toggleButton.setAttribute('aria-checked', 'false');
        }
    });


         if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/serviceworker.js')
                .then(() => console.log("Service worker registered"))
                .catch((e) => console.error("Service worker error:", e));
        }

});