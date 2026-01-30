window.DCB = {
    formatNumber: function (num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    },

    showToast: function (message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `dcb-toast ${type}`;
        toast.style.position = 'fixed';
        toast.style.top = '20px';
        toast.style.right = '20px';
        toast.style.padding = '15px 25px';
        toast.style.borderRadius = '50px';
        toast.style.color = 'white';
        toast.style.fontWeight = 'bold';
        toast.style.zIndex = '9999';
        toast.style.boxShadow = '0 5px 15px rgba(0,0,0,0.2)';
        toast.style.opacity = '0';
        toast.style.transition = 'all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55)';
        toast.style.transform = 'translateY(-20px)';

        // Piggo Colors
        if (type === 'success') toast.style.background = 'linear-gradient(45deg, #22c55e, #a8e063)';
        else if (type === 'error') toast.style.background = 'linear-gradient(45deg, #ff5252, #ff9a9e)';
        else toast.style.background = 'linear-gradient(45deg, #2196f3, #4facfe)';

        toast.innerHTML = `<span style="margin-right:10px;font-size:1.2em">${type === 'success' ? '🐷' : '🐽'}</span> ${message}`;

        document.body.appendChild(toast);

        // Animate in
        setTimeout(() => {
            toast.style.opacity = '1';
            toast.style.transform = 'translateY(0)';
        }, 10);

        // Remove after 3s
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(-20px)';
            setTimeout(() => toast.remove(), 500);
        }, 3000);
    },

    // Confetti effect for winning/purchasing
    celebrate: function () {
        // Simple particle effect could be added here
        console.log("🐷 Oink! Celebration!");
    }
};
