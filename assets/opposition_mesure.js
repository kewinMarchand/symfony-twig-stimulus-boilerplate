export const OPPOSITION_MESURE_KEY = 'mesure-audience-refusee';

/**
 * Opposition à la mesure d'audience (fiche CNIL n° 17, section 16 de la spec commune). Le refus de
 * Matomo passe par un cookie, impossible avec `disableCookies` : il est gardé dans le stockage
 * local. Un stockage indisponible vaut absence de refus.
 */
export const isMesureRefusee = () => {
    try {
        return globalThis.localStorage.getItem(OPPOSITION_MESURE_KEY) === '1';
    } catch {
        return false;
    }
};

export const setOppositionMesure = (refusee) => {
    try {
        if (refusee) {
            globalThis.localStorage.setItem(OPPOSITION_MESURE_KEY, '1');
        } else {
            globalThis.localStorage.removeItem(OPPOSITION_MESURE_KEY);
        }
    } catch {
        // Stockage indisponible : le choix ne peut pas être gardé.
    }
};
