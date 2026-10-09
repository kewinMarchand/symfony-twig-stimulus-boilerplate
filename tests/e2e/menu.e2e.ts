import { expect, test } from '@playwright/test'

import { useA11yMode } from './a11yMode'
import { A11Y_MODES } from './routes'

test.describe('Menu de catégories (desktop)', () => {
  test.skip(({ isMobile }) => isMobile, 'menu en cascade réservé au desktop')

  test('s’ouvre au clic, affiche les enfants au survol et se ferme à Échap', async ({ page }) => {
    await page.goto('/')
    const toggle = page.getByTestId('layout-category-menu-toggle')
    const menu = page.getByTestId('layout-category-menu')

    await expect(menu).toBeHidden()
    await toggle.click()
    await expect(menu).toBeVisible()
    await expect(toggle).toHaveAttribute('aria-expanded', 'true')

    const parent = menu.getByRole('link', { name: 'Plantes d’intérieur' })
    await parent.hover()
    await expect(parent).toHaveAttribute('aria-expanded', 'true')
    await expect(menu.getByRole('link', { name: 'Feuillages' })).toBeVisible()

    await page.keyboard.press('Escape')
    await expect(menu).toBeHidden()
    await expect(toggle).toBeFocused()
  })

  test('ouvre les colonnes au focus clavier', async ({ page }) => {
    await page.goto('/')
    await page.getByTestId('layout-category-menu-toggle').click()
    const menu = page.getByTestId('layout-category-menu')

    await menu.getByRole('link', { name: 'Plantes d’extérieur' }).focus()
    await expect(menu.getByRole('link', { name: 'Palmiers' })).toBeVisible()
    await expect(menu.getByRole('link', { name: 'Feuillages' })).toBeHidden()
  })

  test('une feuille navigue et met à jour le fil d’Ariane', async ({ page }) => {
    await page.goto('/')
    await page.getByTestId('layout-category-menu-toggle').click()
    const menu = page.getByTestId('layout-category-menu')
    await menu.getByRole('link', { name: 'Plantes d’intérieur' }).hover()
    await menu.getByRole('link', { name: 'Feuillages' }).hover()
    await menu.getByRole('link', { name: 'Monstera' }).click()

    await expect(page).toHaveURL(/\/catalogue\/plantes-interieur\/feuillages\/monstera$/)
    await expect(page.getByTestId('layout-breadcrumb').locator('[aria-current="page"]')).toHaveText('Monstera')
  })

  for (const mode of A11Y_MODES) {
    test(`le panneau est ancré à son bouton en mode ${mode}`, async ({ page }) => {
      await useA11yMode(page, mode)
      await page.goto('/catalogue')
      const header = page.locator('header.header')
      const heading = page.getByRole('heading', { level: 1 })
      const headerHeight = (await header.boundingBox())?.height
      const headingTop = (await heading.boundingBox())?.y

      const toggle = page.getByTestId('layout-category-menu-toggle')
      await toggle.click()
      const button = await toggle.boundingBox()
      const panel = await page.getByTestId('layout-category-menu').boundingBox()
      if (!button || !panel) throw new Error('bouton ou panneau introuvable')

      expect(Math.abs(panel.y - (button.y + button.height))).toBeLessThan(12)
      const leftAligned = Math.abs(panel.x - button.x) < 12
      const rightAligned = Math.abs(panel.x + panel.width - (button.x + button.width)) < 12
      expect(leftAligned || rightAligned).toBe(true)
      expect((await header.boundingBox())?.height).toBe(headerHeight)
      expect((await heading.boundingBox())?.y).toBe(headingTop)
      await page.screenshot({ path: `screenshots/menu-${mode}.png` })
    })
  }
})

test.describe('Menu mobile', () => {
  test.skip(({ isMobile }) => !isMobile, 'menu par niveaux réservé au mobile')

  test('navigue par niveaux avec le focus sur le titre de chaque niveau', async ({ page }) => {
    await page.goto('/')
    const toggle = page.getByTestId('layout-mobile-menu-toggle')
    const menu = page.getByTestId('layout-mobile-menu')

    await toggle.click()
    await expect(menu).toBeVisible()
    await expect(toggle).toHaveAttribute('aria-expanded', 'true')

    await menu.getByRole('button', { name: 'Catalogue' }).click()
    await expect(menu.getByRole('heading', { name: 'Catalogue' })).toBeFocused()

    await menu.getByRole('button', { name: 'Plantes d’intérieur' }).click()
    await expect(menu.getByRole('heading', { name: 'Plantes d’intérieur' })).toBeFocused()
    await expect(menu.getByRole('link', { name: 'Voir toute la catégorie' })).toHaveAttribute('href', '/catalogue/plantes-interieur')

    await menu.getByTestId('layout-mobile-menu-back').filter({ visible: true }).click()
    await expect(menu.getByRole('heading', { name: 'Catalogue' })).toBeFocused()

    await page.keyboard.press('Escape')
    await expect(menu).toBeHidden()
    await expect(toggle).toBeFocused()
    await expect(toggle).toHaveAttribute('aria-expanded', 'false')

    await toggle.click()
    await expect(menu.getByRole('heading', { name: 'Menu' })).toBeVisible()
  })

  test('une feuille navigue vers sa catégorie', async ({ page }) => {
    await page.goto('/')
    await page.getByTestId('layout-mobile-menu-toggle').click()
    const menu = page.getByTestId('layout-mobile-menu')
    await menu.getByRole('button', { name: 'Catalogue' }).click()
    await menu.getByRole('button', { name: 'Plantes aquatiques' }).click()
    await menu.getByRole('link', { name: 'Nénuphars' }).click()

    await expect(page).toHaveURL(/\/catalogue\/plantes-aquatiques\/nenuphars$/)
    await expect(page.getByRole('heading', { level: 1 })).toHaveText('Nénuphars')
  })
})
