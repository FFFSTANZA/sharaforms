// Utility to seed the first block with a random abstract image and right-split layout

const ABSTRACT_IMAGES = [
  'https://images.unsplash.com/photo-1519681393784-d120267933ba?ixlib=rb-4.1.0&auto=format&fit=crop&q=80&w=1600',
  'https://images.unsplash.com/photo-1519681393784-d120267933ba?ixlib=rb-4.1.0&auto=format&fit=crop&q=80&w=1600',
  'https://images.unsplash.com/photo-1519681393784-d120267933ba?ixlib=rb-4.1.0&auto=format&fit=crop&q=80&w=1600',
  'https://images.unsplash.com/photo-1519681393784-d120267933ba?ixlib=rb-4.1.0&auto=format&fit=crop&q=80&w=1600',
  'https://images.unsplash.com/photo-1519681393784-d120267933ba?ixlib=rb-4.1.0&auto=format&fit=crop&q=80&w=1600',
  'https://images.unsplash.com/photo-1519681393784-d120267933ba?ixlib=rb-4.1.0&auto=format&fit=crop&q=80&w=1600',
  'https://images.unsplash.com/photo-1519681393784-d120267933ba?ixlib=rb-4.1.0&auto=format&fit=crop&q=80&w=1600',
  'https://images.unsplash.com/photo-1519681393784-d120267933ba?ixlib=rb-4.1.0&auto=format&fit=crop&q=80&w=1600'
]

function getRandomAbstractImageUrl() {
  const index = Math.floor(Math.random() * ABSTRACT_IMAGES.length)
  return ABSTRACT_IMAGES[index]
}

export function seedFocusedFirstBlockImage(content) {
  if (!content || !Array.isArray(content.properties) || content.properties.length === 0) return
  const firstBlock = content.properties[0]
  if (!firstBlock) return
  firstBlock.image = firstBlock.image || {}
  if (!firstBlock.image.url) {
    firstBlock.image.url = getRandomAbstractImageUrl()
  }
  if (!firstBlock.image.layout) {
    firstBlock.image.layout = 'right-split'
  }
}

export default seedFocusedFirstBlockImage


