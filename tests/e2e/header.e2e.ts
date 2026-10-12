import { expect, test } from '@playwright/test'

import { useA11yMode } from './a11yMode'
import { A11Y_MODES } from './routes'

import type { Locator } from '@playwright/test'

const box = async (locator: Locator) => {
  const rect = await locator.boundingBox()
  if (!rect) throw new Error('élément sans boîte')
  return rect
}

test.describe('Header mobile compact', () => {
  test.skip(({ isMobile }) => !isMobile, 'header compact réservé au mobile')

  for (const mode of A11Y_MODES) {
    test(`logo et deux boutons icône sur une seule ligne à 375 px en mode ${mode}`, async ({ page }) => {
      await useA11yMode(page, mode)
      await page.setViewportSize({ width: 375, height: 800 })
      await page.goto('/')

      const logo = await box(page.getByTestId('layout-logo'))
      const menu = page.getByTestId('layout-mobile-menu-toggle')
      const a11y = page.getByTestId('a11y-mode-toggle')
      const menuBox = await box(menu)
      const a11yBox = await box(a11y)

      for (const button of [menuBox, a11yBox]) {
        expect(button.width).toBeGreaterThanOrEqual(44)
        expect(button.height).toBeGreaterThanOrEqual(44)
        expect(button.y).toBeLessThan(logo.y + logo.height)
        expect(button.y + button.height).toBeGreaterThan(logo.y)
      }
      expect(menuBox.x).toBeGreaterThan(logo.x + logo.width - 1)
      expect(a11yBox.x).toBeGreaterThan(menuBox.x)

      await expect(menu).toHaveAccessibleName('Menu')
      await expect(a11y).toHaveAccessibleName('Accessibilité renforcée')
      await page.screenshot({ path: `screenshots/header-mobile-${mode}-375.png`, clip: { x: 0, y: 0, width: 375, height: 120 } })
    })
  }

  test('le bouton Menu ouvre la navigation et garde son état', async ({ page }) => {
    await page.goto('/')
    const menu = page.getByTestId('layout-mobile-menu-toggle')

    await expect(menu).toHaveAttribute('aria-expanded', 'false')
    await menu.click()
    await expect(page.getByTestId('layout-mobile-menu')).toBeVisible()
    await expect(menu).toHaveAttribute('aria-expanded', 'true')
  })

  test('le bouton du mode renforcé garde son état', async ({ page }) => {
    await page.goto('/')
    const a11y = page.getByTestId('a11y-mode-toggle')

    await expect(a11y).toHaveAttribute('aria-pressed', 'false')
    await a11y.click()
    await expect(a11y).toHaveAttribute('aria-pressed', 'true')
  })
})

test.describe('Header desktop', () => {
  test.skip(({ isMobile }) => isMobile, 'libellés visibles réservés au desktop')

  test('le bouton du mode renforcé garde son libellé visible', async ({ page }) => {
    await page.goto('/')

    await expect(page.getByTestId('a11y-mode-toggle')).toContainText('Accessibilité renforcée')
    await expect(page.getByTestId('layout-mobile-menu-toggle')).toBeHidden()
  })
})
