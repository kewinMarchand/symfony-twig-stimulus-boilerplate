import assert from 'node:assert/strict'
import { beforeEach, describe, it } from 'node:test'

import { isMesureRefusee, setOppositionMesure } from '../../assets/opposition_mesure.js'

const createStorage = () => {
  const values = new Map()
  return {
    getItem: (key) => values.get(key) ?? null,
    setItem: (key, value) => values.set(key, value),
    removeItem: (key) => values.delete(key),
  }
}

describe('opposition à la mesure d’audience', () => {
  beforeEach(() => {
    globalThis.localStorage = createStorage()
  })

  it('est absente par défaut', () => {
    assert.equal(isMesureRefusee(), false)
  })

  it('se pose puis se retire', () => {
    setOppositionMesure(true)
    assert.equal(isMesureRefusee(), true)
    setOppositionMesure(false)
    assert.equal(isMesureRefusee(), false)
  })

  it('vaut absence de refus quand le stockage est indisponible', () => {
    globalThis.localStorage = {
      getItem: () => {
        throw new Error('stockage bloqué')
      },
    }
    assert.equal(isMesureRefusee(), false)
  })
})
