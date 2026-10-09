import type { Page } from '@playwright/test'

import type { A11yMode } from './routes'

export const useA11yMode = async (page: Page, mode: A11yMode) => {
  if (mode === 'enhanced') {
    await page.addInitScript(() => window.localStorage.setItem('a11y-mode', 'enhanced'))
  }
}
