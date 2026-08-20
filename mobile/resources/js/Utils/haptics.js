/**
 * Smart Native Haptic Feedback Utility for Mobile ERP
 * Uses Android / Web Vibration API with safe fallback
 */

export const haptic = {
    // Light tap (12ms) - Quick clicks, tabs, filters
    light() {
        if (typeof window !== 'undefined' && 'vibrate' in navigator) {
            try {
                navigator.vibrate(12);
            } catch (e) {}
        }
    },

    // Medium tap (25ms) - Adding item, selecting weight, quantity change
    medium() {
        if (typeof window !== 'undefined' && 'vibrate' in navigator) {
            try {
                navigator.vibrate(25);
            } catch (e) {}
        }
    },

    // Strong tap (45ms) - Deleting item, swiping
    heavy() {
        if (typeof window !== 'undefined' && 'vibrate' in navigator) {
            try {
                navigator.vibrate(45);
            } catch (e) {}
        }
    },

    // Success pattern - Invoice completed, payment received
    success() {
        if (typeof window !== 'undefined' && 'vibrate' in navigator) {
            try {
                navigator.vibrate([20, 40, 30]);
            } catch (e) {}
        }
    },

    // Warning / Error pattern - Validation error
    error() {
        if (typeof window !== 'undefined' && 'vibrate' in navigator) {
            try {
                navigator.vibrate([50, 60, 50]);
            } catch (e) {}
        }
    }
};

export default haptic;
