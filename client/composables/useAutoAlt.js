export function useAutoAlt(baseDescription) {
  function generateAlt(specifics) {
    const parts = ['SharaForms']
    if (specifics) {
      parts.push(specifics)
    }
    if (baseDescription) {
      parts.push(baseDescription)
    }
    return parts.join(' - ')
  }

  return { generateAlt }
}
