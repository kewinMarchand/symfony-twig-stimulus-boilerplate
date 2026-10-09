import { expect, test } from '@playwright/test'

import { useA11yMode } from './a11yMode'
import { A11Y_MODES, ROUTES } from './routes'

const WIDTHS = [375, 768, 1280] as const

test.describe('Mise en page', () => {
  test.skip(({ isMobile }) => isMobile, 'les largeurs sont fixées explicitement, un seul projet suffit')

  for (const mode of A11Y_MODES) {
    for (const width of WIDTHS) {
      test(`aucun débordement horizontal à ${width} px en mode ${mode}`, async ({ page }) => {
        await useA11yMode(page, mode)
        await page.setViewportSize({ width, height: 900 })

        for (const route of ROUTES) {
          await page.goto(route)
          await expect(page.getByRole('heading', { level: 1 })).toBeVisible()

          const overflow = await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth)
          expect(overflow, `${route} déborde de ${overflow} px`).toBeLessThanOrEqual(0)
        }

        for (const route of ['/', '/catalogue']) {
          await page.goto(route)
          await page.screenshot({
            path: `screenshots/${route === '/' ? 'accueil' : 'catalogue'}-${mode}-${width}.png`,
            fullPage: true,
          })
        }
      })
    }
  }

  test('le mode renforcé n’ajoute pas de structure au header ni au-dessus du titre', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 })
    const measure = async () => {
      const results: Record<string, { header: number; heading: number }> = {}
      for (const route of ROUTES) {
        await page.goto(route)
        const header = await page.locator('header.header').boundingBox()
        const heading = await page.getByRole('heading', { level: 1 }).boundingBox()
        if (!header || !heading) throw new Error(`mesure impossible sur ${route}`)
        results[route] = { header: header.height, heading: heading.y }
      }
      return results
    }

    const standard = await measure()
    await page.evaluate(() => window.localStorage.setItem('a11y-mode', 'enhanced'))
    const enhanced = await measure()

    for (const route of ROUTES) {
      expect(enhanced[route].header, `header de ${route}`).toBeLessThanOrEqual(standard[route].header * 1.4)
      expect(enhanced[route].heading, `h1 de ${route}`).toBeLessThanOrEqual(standard[route].heading * 1.4)
    }
  })
})
