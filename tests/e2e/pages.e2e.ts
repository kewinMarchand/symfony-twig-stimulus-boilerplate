import { expect, test } from '@playwright/test'

import { ROUTES } from './routes'

for (const route of ROUTES) {
  test(`la page ${route} répond 200 sans erreur console`, async ({ page }) => {
    const errors: string[] = []
    page.on('console', (message) => {
      if (message.type() === 'error') errors.push(message.text())
    })

    const response = await page.goto(route)

    expect(response?.status()).toBe(200)
    await expect(page.getByRole('heading', { level: 1 })).toBeVisible()
    expect(errors).toEqual([])
  })
}

for (const route of ['/route-inexistante', '/catalogue/categorie-inconnue']) {
  test(`${route} affiche la page 404 avec un lien vers l’accueil`, async ({ page }) => {
    const response = await page.goto(route)

    expect(response?.status()).toBe(404)
    await expect(page.getByRole('heading', { level: 1, name: 'Page introuvable' })).toBeVisible()
    await expect(page.getByTestId('error-home-link')).toHaveAttribute('href', '/')
  })
}

test('le footer reste collé en bas du viewport sur une page courte', async ({ page }) => {
  await page.goto('/route-inexistante')

  const footerBottom = await page
    .getByTestId('layout-footer')
    .evaluate((footer) => footer.getBoundingClientRect().bottom)

  expect(Math.round(footerBottom)).toBe(page.viewportSize()?.height)
})

test('la charte graphique n’existe pas en production', async ({ page }) => {
  const response = await page.goto('/charte-graphique')

  expect(response?.status()).toBe(404)
})

test('la route de test des erreurs 500 n’existe pas en production', async ({ page }) => {
  const response = await page.goto('/_error/500')

  expect(response?.status()).toBe(404)
})
