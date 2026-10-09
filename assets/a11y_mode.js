export const A11Y_MODE_KEY = 'a11y-mode';
export const A11Y_MODE_ENHANCED = 'enhanced';

export const isA11yModeEnhanced = (root) => root.dataset.a11yMode === A11Y_MODE_ENHANCED;

export const toggleA11yMode = (root) => {
    const enhanced = !isA11yModeEnhanced(root);

    if (enhanced) {
        root.dataset.a11yMode = A11Y_MODE_ENHANCED;
    } else {
        delete root.dataset.a11yMode;
    }

    try {
        if (enhanced) {
            globalThis.localStorage.setItem(A11Y_MODE_KEY, A11Y_MODE_ENHANCED);
        } else {
            globalThis.localStorage.removeItem(A11Y_MODE_KEY);
        }
    } catch {
        // Stockage indisponible (navigation privée, cookies bloqués) : le mode reste actif pour la page.
    }

    return enhanced;
};
