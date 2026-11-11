import rawCountries from '../../json/country.json'

type RawCountry = {
  id: string
  sortname: string
  name: string
  phonecode: string
}

export type CountryOption = {
  id: string
  isoCode: string
  name: string
  phonecode: string
  dialCode: string
}

const countries: CountryOption[] = (rawCountries as RawCountry[])
  .map((c) => ({
    id: String(c.id),
    isoCode: String(c.sortname),
    name: String(c.name),
    phonecode: String(c.phonecode ?? ''),
    dialCode: `+${String(c.phonecode ?? '')}`,
  }))
  .sort((a, b) => a.name.localeCompare(b.name))

export function getCountries(): CountryOption[] {
  return countries
}

export function splitInternationalPhoneNumber(
  phone: string | null | undefined,
  dialCodes: string[],
): { dialCode: string; nationalNumber: string } {
  const raw = String(phone ?? '').trim()
  if (!raw) return { dialCode: '', nationalNumber: '' }

  const normalized = raw.replace(/[\s\-()]/g, '')

  if (!normalized.startsWith('+')) {
    return { dialCode: '', nationalNumber: normalized.replace(/\D/g, '') }
  }

  const sortedDialCodes = [...dialCodes].sort((a, b) => b.length - a.length)
  const match = sortedDialCodes.find((dc) => normalized.startsWith(dc))

  if (!match) {
    return { dialCode: '', nationalNumber: normalized.replace(/\D/g, '') }
  }

  const rest = normalized.slice(match.length)
  return { dialCode: match, nationalNumber: rest.replace(/\D/g, '') }
}
