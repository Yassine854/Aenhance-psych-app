import { computed, onMounted, ref, shallowRef } from 'vue'

// Keep the large geography dataset out of the form's initial render path.
export function useProfileCities(form, countryField = 'country', cityField = 'city') {
  const geography = shallowRef(null)
  const citiesLoading = ref(true)

  onMounted(async () => {
    try {
      geography.value = await import('../utils/geoData')
    } catch (error) {
      console.warn('Unable to load city options:', error)
    } finally {
      citiesLoading.value = false
    }
  })

  const cities = computed(() => {
    const names = geography.value?.getCitiesByCountryName(form[countryField]).map(city => city.name) || []
    // Preserve saved values while loading, and cities absent from the dataset.
    const selectedCity = form[cityField]
    return selectedCity && !names.includes(selectedCity) ? [selectedCity, ...names] : names
  })

  return { cities, citiesLoading }
}
