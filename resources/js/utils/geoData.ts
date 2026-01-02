import rawCountries from '../../json/country.json'
import rawStates from '../../json/state.json'
import rawCities from '../../json/city.json'

type RawCountry = {
  id: string
  sortname: string
  name: string
  phonecode: string
}

type RawState = {
  id: string
  name: string
  country_id: string
}

type RawCity = {
  id: string
  name: string
  state_id: string
}

export type CountryOption = {
  id: string
  isoCode: string
  name: string
  phonecode: string
  dialCode: string
}

export type StateOption = {
  id: string
  name: string
  countryId: string
}

export type CityOption = {
  id: string
  name: string
  stateId: string
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

const states: StateOption[] = (rawStates as RawState[]).map((s) => ({
  id: String(s.id),
  name: String(s.name),
  countryId: String(s.country_id),
}))

const cities: CityOption[] = (rawCities as RawCity[]).map((c) => ({
  id: String(c.id),
  name: String(c.name),
  stateId: String(c.state_id),
}))

export function getCountries(): CountryOption[] {
  return countries
}

export function getStates(): StateOption[] {
  return states
}

export function getStatesByCountry(countryId: string): StateOption[] {
  return states.filter((s) => s.countryId === countryId).sort((a, b) => a.name.localeCompare(b.name))
}

export function getStatesByCountryName(countryName: string): StateOption[] {
  const country = countries.find((c) => c.name === countryName)
  return country ? getStatesByCountry(country.id) : []
}

export function getCities(): CityOption[] {
  return cities
}

export function getCitiesByState(stateId: string): CityOption[] {
  return cities.filter((c) => c.stateId === stateId).sort((a, b) => a.name.localeCompare(b.name))
}

export function getCitiesByCountry(countryId: string): CityOption[] {
  const countryStates = getStatesByCountry(countryId)
  const stateIds = new Set(countryStates.map((s) => s.id))
  return cities.filter((c) => stateIds.has(c.stateId)).sort((a, b) => a.name.localeCompare(b.name))
}

export function getCitiesByCountryName(countryName: string): CityOption[] {
  const country = countries.find((c) => c.name === countryName)
  return country ? getCitiesByCountry(country.id) : []
}

let cityNamesPromise: Promise<string[]> | null = null

export async function loadCityNames(): Promise<string[]> {
  if (cityNamesPromise) return cityNamesPromise

  cityNamesPromise = Promise.resolve(
    Array.from(new Set(cities.map((c) => c.name))).sort((a, b) => a.localeCompare(b))
  )

  return cityNamesPromise
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
