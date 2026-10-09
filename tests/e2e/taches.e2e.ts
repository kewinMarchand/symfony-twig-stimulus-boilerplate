import { expect, test } from '@playwright/test'

test.describe('Tâches', () => {
  test('affiche la liste chargée dans le Turbo Frame', async ({ page }) => {
    await page.goto('/taches')

    await expect(page.getByTestId('tasks-list')).toBeVisible()
    await expect(page.getByTestId('tasks-item')).toHaveCount(3)
  })

  test("n'affiche ni chargement, ni erreur, ni liste vide quand les données sont là", async ({ page }) => {
    await page.goto('/taches')

    await expect(page.getByTestId('tasks-list')).toBeVisible()
    await expect(page.getByTestId('tasks-loading')).toHaveCount(0)
    await expect(page.getByTestId('tasks-error')).toHaveCount(0)
    await expect(page.getByTestId('tasks-empty')).toHaveCount(0)
  })
})

test.describe('Tâches sans JavaScript', () => {
  test.use({ javaScriptEnabled: false })

  test('un lien remplace le squelette et mène à la liste rendue par le serveur', async ({ page }) => {
    await page.goto('/taches')

    await expect(page.getByTestId('tasks-loading')).toBeHidden()
    await page.getByTestId('tasks-noscript-link').click()

    await expect(page).toHaveURL(/\/taches\/liste$/)
    await expect(page.getByRole('heading', { level: 1, name: 'Tâches' })).toBeVisible()
    await expect(page.getByTestId('tasks-item')).toHaveCount(3)
    await expect(page.locator('link[rel="canonical"]')).toHaveAttribute('href', /\/taches$/)
  })
})
