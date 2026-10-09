import assert from 'node:assert/strict'
import { beforeEach, describe, it } from 'node:test'

import { isA11yModeEnhanced, toggleA11yMode } from '../../assets/a11y_mode.js'

const createStorage = () => {
  const values = new Map()
  return {
    getItem: (key) => values.get(key) ?? null,
    setItem: (key, value) => values.set(key, value),
    removeItem: (key) => values.delete(key),
  }
}

describe('toggleA11yMode', () => {
  let root

  beforeEach(() => {
    root = { dataset: {} }
    globalThis.localStorage = createStorage()
  })

  it('active le mode, pose l’attribut et le mémorise', () => {
    const pressed = toggleA11yMode(root)

    assert.equal(pressed, true)
    assert.equal(String(pressed), 'true')
    assert.equal(root.dataset.a11yMode, 'enhanced')
    assert.equal(globalThis.localStorage.getItem('a11y-mode'), 'enhanced')
  })

  it('désactive le mode, retire l’attribut et l’oublie', () => {
    toggleA11yMode(root)
    const pressed = toggleA11yMode(root)

    assert.equal(String(pressed), 'false')
    assert.equal(isA11yModeEnhanced(root), false)
    assert.equal('a11yMode' in root.dataset, false)
    assert.equal(globalThis.localStorage.getItem('a11y-mode'), null)
  })

  it('reste utilisable quand le stockage est indisponible', () => {
    globalThis.localStorage = {
      setItem: () => {
        throw new Error('SecurityError')
      },
    }

    assert.equal(toggleA11yMode(root), true)
    assert.equal(root.dataset.a11yMode, 'enhanced')
  })
})
