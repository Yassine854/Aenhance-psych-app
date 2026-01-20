<template>
  <div v-if="show && psychologist" class="fixed inset-0 z-[1000] flex items-center justify-center">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[1000]" @click="$emit('close')"></div>
    <div class="relative w-full max-w-7xl rounded-2xl shadow-2xl overflow-hidden z-[1001]">
      <!-- Gradient header with avatar -->
      <div class="bg-gradient-to-r from-[rgb(141,61,79)] to-[rgb(89,151,172)] p-6">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-4">
            <img v-if="psychologist.profile_image_url" :src="psychologist.profile_image_url" class="h-14 w-14 rounded-full ring-2 ring-white/70 object-cover" />
            <div v-else class="h-14 w-14 rounded-full bg-white/20 flex items-center justify-center text-white">No</div>
            <div class="text-white">
              <div class="text-xl font-semibold">{{ psychologist.first_name }} {{ psychologist.last_name }}</div>
              <div class="text-sm opacity-90">
                Psychologist #{{ psychologist.id }} • {{ (psychologist.specialisations || []).map(s => s.name).join(', ') || '—' }}
              </div>
            </div>
          </div>
              <div class="flex items-center gap-3">
            <div v-if="section === 'verification'">
              <select v-model="verificationForm.verification_status" @change="handleStatusChange" :class="[verificationForm.verification_status === 'approved' ? 'bg-green-600' : (verificationForm.verification_status === 'rejected' ? 'bg-red-600' : 'bg-yellow-500'), 'text-white rounded-full px-5 py-1 text-sm font-semibold focus:outline-none w-40']">
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
              </select>
            </div>
            <button @click="$emit('close')" class="text-white/90 hover:text-white text-2xl leading-none">✕</button>
          </div>
        </div>
        <!-- Tabs -->
        <div class="mt-4 flex items-center gap-2">
          <button @click="section='profile'" :class="section==='profile' ? 'bg-white text-gray-900 shadow' : 'bg-white/20 text-white hover:bg-white/30'" class="px-4 py-2 rounded-lg transition">
            Profile Details
          </button>
          <button @click="section='account'" :class="section==='account' ? 'bg-white text-gray-900 shadow' : 'bg-white/20 text-white hover:bg-white/30'" class="px-4 py-2 rounded-lg transition">
            Account
          </button>
          <button @click="section='verification'" :class="section==='verification' ? 'bg-white text-gray-900 shadow' : 'bg-white/20 text-white hover:bg-white/30'" class="px-4 py-2 rounded-lg transition">
            Verification Details
          </button>
        </div>
      </div>
      <!-- Content -->
      <div class="bg-white p-6 max-h-[70vh] overflow-y-auto styled-scrollbar">
        <!-- Profile section -->
        <form v-if="section==='profile'" @submit.prevent="submitProfile" class="space-y-4">
          <!-- General error message -->
          <div v-if="generalError" class="p-4 bg-red-50 border border-red-200 rounded-lg">
            <div class="flex items-start gap-3">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600 flex-shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
              </svg>
              <div class="flex-1">
                <h3 class="text-sm font-medium text-red-800">Error saving profile</h3>
                <p class="mt-1 text-sm text-red-700">{{ generalError }}</p>
              </div>
              <button @click="generalError = null" type="button" class="text-red-400 hover:text-red-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
              </button>
            </div>
          </div>
          
          <!-- Row 1: First, Last -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
              <label class="text-sm font-medium text-gray-700">First name <span class="text-red-500">*</span></label>
              <input v-model="form.first_name" class="mt-1 block w-full rounded-md border-gray-300 focus:ring-2 focus:ring-[rgb(89,151,172)]" />
              <p v-if="form.errors.first_name" class="mt-1 text-sm text-red-600">{{ form.errors.first_name }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-700">Last name <span class="text-red-500">*</span></label>
              <input v-model="form.last_name" class="mt-1 block w-full rounded-md border-gray-300 focus:ring-2 focus:ring-[rgb(89,151,172)]" />
              <p v-if="form.errors.last_name" class="mt-1 text-sm text-red-600">{{ form.errors.last_name }}</p>
            </div>
          </div>

          <!-- Row 2: Specialisations, Expertises, Languages -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div>
              <label class="text-sm font-medium text-gray-700">Specialisations <span class="text-red-500">*</span></label>
              <div class="mt-1">
                <Multiselect
                  v-model="form.specialisation_ids"
                  :options="specialisationOptions"
                  mode="tags"
                  :close-on-select="false"
                  :searchable="true"
                  placeholder="Select one or more"
                />
              </div>
              <p v-if="form.errors.specialisation_ids" class="mt-1 text-sm text-red-600">{{ form.errors.specialisation_ids }}</p>
            </div>

            <div>
              <label class="text-sm font-medium text-gray-700">Expertises</label>
              <div class="mt-1">
                <Multiselect
                  v-model="form.expertise_ids"
                  :options="expertiseOptions"
                  mode="tags"
                  :close-on-select="false"
                  :searchable="true"
                  placeholder="Select one or more"
                />
              </div>
              <p v-if="form.errors.expertise_ids" class="mt-1 text-sm text-red-600">{{ form.errors.expertise_ids }}</p>
            </div>

            <div>
              <label class="text-sm font-medium text-gray-700">Languages <span class="text-red-500">*</span></label>
              <div class="mt-1">
                <Multiselect
                  v-model="form.languages"
                  :options="languageOptions"
                  mode="tags"
                  :close-on-select="false"
                  :searchable="true"
                  placeholder="Select one or more"
                />
              </div>
              <p v-if="form.errors.languages" class="mt-1 text-sm text-red-600">{{ form.errors.languages }}</p>
            </div>
          </div>

          <!-- Row 2: Gender, Date of Birth, Price -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div>
              <label class="text-sm font-medium text-gray-700">Gender</label>
              <select v-model="form.gender" class="mt-1 block w-full rounded-md border-gray-300 focus:ring-2 focus:ring-[rgb(89,151,172)]">
                <option value="">Select gender</option>
                  <option value="MALE">Male</option>
                  <option value="FEMALE">Female</option>
                  <option value="OTHER">Other</option>
              </select>
              <p v-if="form.errors.gender" class="mt-1 text-sm text-red-600">{{ form.errors.gender }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-700">Date of birth <span class="text-red-500">*</span></label>
              <input type="date" v-model="form.date_of_birth" class="mt-1 block w-full rounded-md border-gray-300 focus:ring-2 focus:ring-[rgb(89,151,172)]" />
              <p v-if="form.errors.date_of_birth" class="mt-1 text-sm text-red-600">{{ form.errors.date_of_birth }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-700">Price per session (DT) <span class="text-red-500">*</span></label>
              <div class="mt-1 flex">
                <input type="number" step="0.01" v-model="form.price_per_session" class="block w-full rounded-l-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-[rgb(89,151,172)]" />
                <span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-700 text-sm font-medium">DT</span>
              </div>
              <p v-if="form.errors.price_per_session" class="mt-1 text-sm text-red-600">{{ form.errors.price_per_session }}</p>
            </div>
          </div>

          <!-- Row 3: Country, City, Phone -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div>
              <label class="text-sm font-medium text-gray-700">Country <span class="text-red-500">*</span></label>
              <select v-model="countryCode" class="mt-1 block w-full rounded-md border-gray-300 focus:ring-2 focus:ring-[rgb(89,151,172)]">
                <option value="">Select country</option>
                <option v-for="c in countriesList" :key="c.isoCode" :value="c.isoCode">{{ c.name }}</option>
              </select>
              <p v-if="form.errors.country" class="mt-1 text-sm text-red-600">{{ form.errors.country }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-700">City <span class="text-red-500">*</span></label>
              <select v-model="form.city" class="mt-1 block w-full rounded-md border-gray-300 focus:ring-2 focus:ring-[rgb(89,151,172)]">
                <option value="">Select city</option>
                <option v-for="ct in cities" :key="ct" :value="ct">{{ ct }}</option>
              </select>
              <p v-if="form.errors.city" class="mt-1 text-sm text-red-600">{{ form.errors.city }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-700">Phone <span class="text-red-500">*</span></label>
              <div class="mt-1 flex">
                <input v-model="dialCode" readonly class="w-24 rounded-l-md border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-700" placeholder="+___" />
                <input v-model="nationalNumber" inputmode="tel" class="block w-full rounded-r-md border border-l-0 border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-[rgb(89,151,172)]" placeholder="Enter phone number" />
              </div>
              <p v-if="form.errors.phone" class="mt-1 text-sm text-red-600">{{ form.errors.phone }}</p>
            </div>
          </div>

          <!-- Row 4: Address and Approved -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div class="md:col-span-2">
              <label class="text-sm font-medium text-gray-700">Address</label>
              <input v-model="form.address" class="mt-1 block w-full rounded-md border-gray-300 focus:ring-2 focus:ring-[rgb(89,151,172)]" />
              <p v-if="form.errors.address" class="mt-1 text-sm text-red-600">{{ form.errors.address }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-700">Approved</label>
              <select v-model="form.is_approved" class="mt-1 block w-full rounded-md border-gray-300 focus:ring-2 focus:ring-[rgb(89,151,172)]">
                <option :value="true">Yes</option>
                <option :value="false">No</option>
              </select>
              <p v-if="form.errors.is_approved" class="mt-1 text-sm text-red-600">{{ form.errors.is_approved }}</p>
            </div>
          </div>

          <!-- Uploads: Diploma, CV, Profile image -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div>
              <label class="text-sm font-medium text-gray-700">Diploma (PDF) <span class="text-red-500">*</span></label>
              <div class="mt-1 group relative flex flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 p-4 text-center cursor-pointer transition hover:bg-gray-100 hover:border-[rgb(89,151,172)]" @click="() => diplomaInput?.click()" @dragover.prevent @drop.prevent="onDrop('diploma_files', $event)">
                <input ref="diplomaInput" type="file" accept="application/pdf" class="hidden" @change="onFileChange('diploma_files', $event)" multiple />
                <div class="flex items-center gap-2 text-gray-600">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="currentColor"><path d="M6 2h9l5 5v13a2 2 0 01-2 2H6a2 2 0 01-2-2V4a2 2 0 012-2zm8 1.5V8h4.5L14 3.5z"/></svg>
                  <span class="text-xs">{{ diplomaLabel }}</span>
                </div>
              </div>
              <p v-if="form.errors.diploma_files || form.errors.diploma_file || form.errors.diploma" class="mt-1 text-sm text-red-600">{{ form.errors.diploma_files || form.errors.diploma_file || form.errors.diploma }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-700">CV (PDF) <span class="text-red-500">*</span></label>
              <div class="mt-1 group relative flex flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 p-4 text-center cursor-pointer transition hover:bg-gray-100 hover:border-[rgb(89,151,172)]" @click="() => cvInput?.click()" @dragover.prevent @drop.prevent="onDrop('cv_file', $event)">
                <input ref="cvInput" type="file" accept="application/pdf" class="hidden" @change="onFileChange('cv_file', $event)" />
                <div class="flex items-center gap-2 text-gray-600">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="currentColor"><path d="M6 2h9l5 5v13a2 2 0 01-2 2H6a2 2 0 01-2-2V4a2 2 0 012-2zm8 1.5V8h4.5L14 3.5z"/></svg>
                  <span class="text-xs">{{ cvLabel }}</span>
                </div>
              </div>
              <p v-if="form.errors.cv_file || form.errors.cv" class="mt-1 text-sm text-red-600">{{ form.errors.cv_file || form.errors.cv }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-700">Profile image</label>
              <div class="mt-1 group relative flex flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 p-4 text-center cursor-pointer transition hover:bg-gray-100 hover:border-[rgb(89,151,172)]" @click="() => profileInput?.click()" @dragover.prevent @drop.prevent="onDrop('profile_image', $event)">
                <input ref="profileInput" type="file" accept="image/*" class="hidden" @change="onFileChange('profile_image', $event)" />
                <div v-if="imagePreview" class="flex flex-col items-center gap-2">
                  <img :src="imagePreview" class="h-16 w-16 rounded-full object-cover ring-1 ring-gray-200" />
                  <span class="text-xs text-gray-600">Click to replace</span>
                </div>
                <div v-else class="flex flex-col items-center gap-2 text-gray-500">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 24 24" fill="currentColor"><path d="M12 5a7 7 0 100 14 7 7 0 000-14zm0 2a2.5 2.5 0 11-.001 5.001A2.5 2.5 0 0112 7zm0 10a5.5 5.5 0 01-4.686-2.5 3.5 3.5 0 016.372 0A5.5 5.5 0 0112 17z"/></svg>
                  <span class="text-xs">Drag & drop or click</span>
                </div>
              </div>
              <p v-if="form.errors.profile_image || form.errors.profile_image_url" class="mt-1 text-sm text-red-600">{{ form.errors.profile_image || form.errors.profile_image_url }}</p>
            </div>
          </div>

          <div>
            <label class="text-sm font-medium text-gray-700">Bio</label>
            <textarea v-model="form.bio" class="mt-1 block w-full rounded-md border-gray-300 h-28 focus:ring-2 focus:ring-[rgb(89,151,172)]"></textarea>
            <p v-if="form.errors.bio" class="mt-1 text-sm text-red-600">{{ form.errors.bio }}</p>
          </div>

          <!-- AVAILABILITY SECTION -->
          <div class="border-t border-gray-200 pt-6">
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-semibold text-gray-900">Availability <span class="text-red-500">*</span></h3>
              <div class="text-sm text-gray-500">Weekly slots (day + time)</div>
            </div>

            <p v-if="availabilityRequiredError" class="mt-2 text-sm text-red-600">
              {{ availabilityRequiredError }}
            </p>

            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
              <div v-for="d in daysOfWeek" :key="d.value" class="rounded-xl border border-gray-200 bg-white p-4">
                <div class="flex items-center justify-between">
                  <div class="font-semibold text-gray-900">{{ d.label }}</div>
                  <button
                    type="button"
                    @click="addSlotForDay(d.value)"
                    class="px-3 py-1.5 text-sm bg-[rgb(89,151,172)] text-white rounded-lg shadow hover:opacity-90 font-medium"
                  >
                    Add slot
                  </button>
                </div>

                <div v-if="availabilityByDay[d.value].length" class="mt-3 space-y-2">
                  <div
                    v-for="(slot, idx) in availabilityByDay[d.value]"
                    :key="idx"
                    class="flex items-end gap-2"
                  >
                    <div class="flex-1">
                      <label class="text-xs font-medium text-gray-600">Start</label>
                      <input
                        type="time"
                        v-model="slot.start_time"
                        @change="onSlotChanged(d.value)"
                        class="mt-1 block w-full rounded-md border-gray-300 focus:ring-2 focus:ring-[rgb(89,151,172)]"
                      />
                    </div>
                    <div class="flex-1">
                      <label class="text-xs font-medium text-gray-600">End</label>
                      <input
                        type="time"
                        v-model="slot.end_time"
                        @change="onSlotChanged(d.value)"
                        class="mt-1 block w-full rounded-md border-gray-300 focus:ring-2 focus:ring-[rgb(89,151,172)]"
                      />
                    </div>
                    <button
                      type="button"
                      @click="removeSlotForDay(d.value, idx)"
                      class="px-3 py-2 text-sm text-red-600 hover:text-red-800"
                    >
                      Remove
                    </button>
                  </div>
                </div>

                <div v-else class="mt-3 text-sm text-gray-500">
                  No slots added.
                </div>

                <p v-if="availabilityErrorsByDay[d.value]" class="mt-2 text-sm text-red-600">
                  {{ availabilityErrorsByDay[d.value] }}
                </p>
              </div>
            </div>
          </div>

          <div class="flex items-center gap-3">
            <button :disabled="saving" class="px-4 py-2 bg-[rgb(141,61,79)] text-white rounded-lg shadow hover:opacity-90">Save Profile</button>
            <button type="button" @click="$emit('close')" class="text-sm text-gray-600">Cancel</button>
          </div>
        </form>

        <!-- Account section -->
        <form v-else-if="section==='account'" @submit.prevent="submitAccount" class="space-y-6">
          <div v-if="accountError" class="p-4 bg-red-50 border border-red-200 rounded-lg">
            <div class="flex items-start gap-3">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600 flex-shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
              </svg>
              <div class="flex-1">
                <h3 class="text-sm font-medium text-red-800">Error saving account</h3>
                <p class="mt-1 text-sm text-red-700">{{ accountError }}</p>
              </div>
              <button @click="accountError = ''" type="button" class="text-red-400 hover:text-red-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
              </button>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="text-sm font-medium text-gray-700">Account name <span class="text-red-500">*</span></label>
              <input v-model="accountForm.name" class="mt-1 block w-full rounded-md border-gray-300 focus:ring-2 focus:ring-[rgb(89,151,172)]" />
            </div>
            <div>
              <label class="text-sm font-medium text-gray-700">Email <span class="text-red-500">*</span></label>
              <input v-model="accountForm.email" type="email" class="mt-1 block w-full rounded-md border-gray-300 focus:ring-2 focus:ring-[rgb(89,151,172)]" />
            </div>
            <div class="md:col-span-2">
              <label class="text-sm font-medium text-gray-700">Password</label>
              <input v-model="accountForm.password" type="password" placeholder="Leave empty to keep current" class="mt-1 block w-full rounded-md border-gray-300 focus:ring-2 focus:ring-[rgb(89,151,172)]" />
              <p class="mt-2 text-xs text-gray-500">If left blank, the password remains unchanged.</p>
            </div>
          </div>

          <p v-if="!psychologist.user" class="text-sm text-yellow-700 bg-yellow-50 border border-yellow-200 rounded px-3 py-2">Link a user to enable account editing.</p>

          <div class="flex items-center gap-3">
            <button type="submit" :disabled="accountSaving || !psychologist.user" class="px-4 py-2 bg-[rgb(89,151,172)] text-white rounded-lg shadow hover:opacity-90">Save Account</button>
            <button v-if="psychologist.user" type="button" @click="toggleActivation" class="px-4 py-2 rounded-lg" :class="psychologist.user.is_active ? 'bg-yellow-600 text-white hover:bg-yellow-700' : 'bg-green-600 text-white hover:bg-green-700'">
              {{ psychologist.user.is_active ? 'Deactivate' : 'Activate' }}
            </button>
            <button type="button" @click="$emit('close')" class="text-sm text-gray-600">Cancel</button>
          </div>
        </form>

        <!-- Verification section -->
        <form v-else-if="section==='verification'" @submit.prevent="submitVerification" class="space-y-6">
          <div v-if="verificationError" class="p-4 bg-red-50 border border-red-200 rounded-lg">
            <div class="flex items-start gap-3">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600 flex-shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
              </svg>
              <div class="flex-1">
                <h3 class="text-sm font-medium text-red-800">Error saving verification</h3>
                <p class="mt-1 text-sm text-red-700">{{ verificationError }}</p>
              </div>
              <button @click="verificationError = ''" type="button" class="text-red-400 hover:text-red-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
              </button>
            </div>
          </div>

          

          <!-- Bank Information Section -->
          <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="bg-gradient-to-r from-[#5997ac] to-[#7ba8b7] px-6 py-4">
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                  <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                  </svg>
                </div>
                <div>
                  <h2 class="text-lg font-semibold text-white">Bank Information</h2>
                  <p class="text-sm text-white/80">Banking details for payments</p>
                </div>
                
              </div>
            </div>
            <div class="p-6 sm:p-8">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                  <label class="text-sm font-medium text-gray-700">RIB (Relevé d'Identité Bancaire) <span class="text-red-500">*</span></label>
                  <input v-model="verificationForm.rib" type="text" class="mt-1 block w-full rounded-md border-gray-300 focus:ring-2 focus:ring-[#5997ac]" placeholder="Enter RIB number" />
                  <p v-if="verificationErrors.rib" class="mt-1 text-sm text-red-600">{{ verificationErrors.rib }}</p>
                </div>

                <div class="space-y-2">
                  <label class="text-sm font-medium text-gray-700">Bank Name <span class="text-red-500">*</span></label>
                  <input v-model="verificationForm.bank_name" type="text" class="mt-1 block w-full rounded-md border-gray-300 focus:ring-2 focus:ring-[#5997ac]" placeholder="Enter bank name" />
                  <p v-if="verificationErrors.bank_name" class="mt-1 text-sm text-red-600">{{ verificationErrors.bank_name }}</p>
                </div>

                <div class="space-y-2">
                  <label class="text-sm font-medium text-gray-700">Account Number <span class="text-red-500">*</span></label>
                  <input v-model="verificationForm.bank_account_number" type="text" class="mt-1 block w-full rounded-md border-gray-300 focus:ring-2 focus:ring-[#5997ac]" placeholder="Enter account number" />
                  <p v-if="verificationErrors.bank_account_number" class="mt-1 text-sm text-red-600">{{ verificationErrors.bank_account_number }}</p>
                </div>

                <div class="space-y-2">
                  <label class="text-sm font-medium text-gray-700">Account Holder Name <span class="text-red-500">*</span></label>
                  <input v-model="verificationForm.bank_account_name" type="text" class="mt-1 block w-full rounded-md border-gray-300 focus:ring-2 focus:ring-[#5997ac]" placeholder="Enter account holder name" />
                  <p v-if="verificationErrors.bank_account_name" class="mt-1 text-sm text-red-600">{{ verificationErrors.bank_account_name }}</p>
                </div>

                <div class="md:col-span-2 space-y-2">
                  <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-[#5997ac]/10 rounded-lg flex items-center justify-center">
                      <svg class="w-5 h-5 text-[#5997ac]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                      </svg>
                    </div>
                    <div>
                      <h3 class="text-sm font-semibold text-gray-900">RIB Document <span class="text-red-500">*</span></h3>
                      <p class="text-xs text-gray-600">Upload bank statement document</p>
                    </div>
                  </div>

                  <div class="mt-1 group relative flex flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 p-4 text-center cursor-pointer transition hover:bg-gray-100 hover:border-[#5997ac]" @click="() => ribFileInput?.click()" @dragover.prevent @drop.prevent="onDropVerification('rib_file', $event)">
                    <input ref="ribFileInput" type="file" accept=".pdf,.jpg,.jpeg,.png" class="hidden" @change="onFileChangeVerification('rib_file', $event)" />
                    <div class="flex items-center gap-2 text-gray-600">
                      <svg class="w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                      </svg>
                      <span class="text-xs">{{ ribFileLabel }}</span>
                    </div>
                  </div>
                  <p v-if="verificationErrors.rib_file || verificationErrors.rib_file_url" class="mt-1 text-sm text-red-600">{{ verificationErrors.rib_file || verificationErrors.rib_file_url }}</p>
                  <div class="text-xs text-gray-500 bg-gray-50 p-2 rounded-lg">Accepted formats: PDF, JPG, PNG. Maximum 5MB.</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Identity Information Section -->
          <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="bg-gradient-to-r from-[#5997ac] to-[#7ba8b7] px-6 py-4">
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                  <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                  </svg>
                </div>
                <div>
                  <h2 class="text-lg font-semibold text-white">Identity Information</h2>
                  <p class="text-sm text-white/80">Personal identification details</p>
                </div>
              </div>
            </div>
            <div class="p-6 sm:p-8">
              <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="text-sm font-medium text-gray-700">Identity Type <span class="text-red-500">*</span></label>
                    <select v-model="verificationForm.identity_type" class="mt-1 block w-full rounded-md border-gray-300 focus:ring-2 focus:ring-[#5997ac]">
                      <option value="">Select type</option>
                      <option value="CIN">CIN</option>
                      <option value="PASSPORT">Passport</option>
                      <option value="DRIVING_LICENSE">Driving License</option>
                    </select>
                      <p v-if="verificationErrors.identity_type" class="mt-1 text-sm text-red-600">{{ verificationErrors.identity_type }}</p>
                  </div>
                  <div>
                    <label class="text-sm font-medium text-gray-700">Identity Number <span class="text-red-500">*</span></label>
                    <input v-model="verificationForm.identity_number" type="text" class="mt-1 block w-full rounded-md border-gray-300 focus:ring-2 focus:ring-[#5997ac]" placeholder="Enter identity number" />
                    <p v-if="verificationErrors.identity_number" class="mt-1 text-sm text-red-600">{{ verificationErrors.identity_number }}</p>
                  </div>
                </div>

                <div class="space-y-2">
                  <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-[#5997ac]/10 rounded-lg flex items-center justify-center">
                      <svg class="w-5 h-5 text-[#5997ac]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                      </svg>
                    </div>
                    <div>
                      <h3 class="text-sm font-semibold text-gray-900">Identity Document <span class="text-red-500">*</span></h3>
                      <p class="text-xs text-gray-600">Upload identification document</p>
                    </div>
                  </div>

                  <div class="mt-1 group relative flex flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 p-4 text-center cursor-pointer transition hover:bg-gray-100 hover:border-[#5997ac]" @click="() => identityFileInput?.click()" @dragover.prevent @drop.prevent="onDropVerification('identity_file', $event)">
                    <input ref="identityFileInput" type="file" accept=".pdf,.jpg,.jpeg,.png" class="hidden" @change="onFileChangeVerification('identity_file', $event)" />
                    <div class="flex items-center gap-2 text-gray-600">
                      <svg class="w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                      </svg>
                      <span class="text-xs">{{ identityFileLabel }}</span>
                    </div>
                  </div>
                  <p v-if="verificationErrors.identity_file || verificationErrors.identity_files_urls" class="mt-1 text-sm text-red-600">{{ verificationErrors.identity_file || verificationErrors.identity_files_urls }}</p>
                  <div class="text-xs text-gray-500 bg-gray-50 p-2 rounded-lg">Accepted formats: PDF, JPG, PNG. Maximum 5MB.</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Diplomas Section -->
          <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="bg-gradient-to-r from-[#5997ac] to-[#7ba8b7] px-6 py-4">
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                  <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z M12 14l9-5-9-5-9 5 9 5z"></path>
                  </svg>
                </div>
                <div>
                  <h2 class="text-lg font-semibold text-white">Professional Diplomas</h2>
                  <p class="text-sm text-white/80">Educational qualifications</p>
                </div>
              </div>
            </div>
            <div class="p-6 sm:p-8">
              <div class="space-y-4">
                <div class="flex items-center gap-3 mb-4">
                  <div class="w-10 h-10 bg-[#5997ac]/10 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-[#5997ac]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                  </div>
                  <div>
                    <h3 class="text-sm font-semibold text-gray-900">Diplomas Copies <span class="text-red-500">*</span></h3>
                    <p class="text-xs text-gray-600">Upload professional qualifications</p>
                  </div>
                </div>

                <div class="mt-1 group relative flex flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 p-4 text-center cursor-pointer transition hover:bg-gray-100 hover:border-[#5997ac]" @click="() => diplomaFileInput?.click()" @dragover.prevent @drop.prevent="onDropVerification('diploma_files', $event)">
                  <input ref="diplomaFileInput" type="file" multiple accept=".pdf,.jpg,.jpeg,.png" class="hidden" @change="onFileChangeVerification('diploma_files', $event)" />
                  <div class="flex items-center gap-2 text-gray-600">
                    <svg class="w-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                    <span class="text-xs">{{ diplomaFilesLabel }}</span>
                  </div>
                </div>
                <p v-if="verificationErrors.diploma_files" class="mt-1 text-sm text-red-600">{{ verificationErrors.diploma_files }}</p>
                <div class="text-xs text-gray-500 bg-gray-50 p-2 rounded-lg">Accepted formats: PDF, JPG, PNG. Maximum 5MB each.</div>
              </div>
            </div>
          </div>

          <div class="flex items-center gap-3">
            <button :disabled="verificationSaving" class="px-4 py-2 bg-[#5997ac] text-white rounded-lg shadow hover:opacity-90">Save Verification</button>
            <button type="button" @click="$emit('close')" class="text-sm text-gray-600">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { Inertia } from '@inertiajs/inertia'
import { getCountries, getCitiesByCountryName, splitInternationalPhoneNumber } from '@/utils/geoData'
import Multiselect from '@vueform/multiselect'
import Swal from 'sweetalert2'

const props = defineProps({
  show: Boolean,
  psychologist: Object,
  specialisations: {
    type: Array,
    default: () => [],
  },
  expertises: {
    type: Array,
    default: () => [],
  },
})

const emit = defineEmits(['close', 'saved'])

const specialisationOptions = computed(() =>
  (props.specialisations || []).map((s) => ({ value: s.id, label: s.name }))
)

const expertiseOptions = computed(() =>
  (props.expertises || []).map((e) => ({ value: e.id, label: e.name }))
)

const languageOptions = [
  { value: 'english', label: 'English' },
  { value: 'french', label: 'French' },
  { value: 'arabic', label: 'Arabic' },
]

const section = ref('profile')
const countriesList = ref(getCountries())
const countryCode = ref('')
const dialCode = ref('')
const nationalNumber = ref('')
const saving = ref(false)
const generalError = ref(null)
const accountSaving = ref(false)
const accountError = ref('')
const accountForm = ref({ name: '', email: '', password: '' })

const verificationSaving = ref(false)
const verificationError = ref('')
const verificationErrors = ref({})
const verificationForm = ref({
  rib: '',
  bank_name: '',
  bank_account_number: '',
  bank_account_name: '',
  rib_file: null,
  identity_type: '',
  identity_number: '',
  identity_file: null,
  diploma_files: [],
  verification_status: 'pending',
  rejection_reason: '',
})

const remoteVerification = ref(null)

const ribFileInput = ref(null)
const identityFileInput = ref(null)
const diplomaFileInput = ref(null)

const diplomaInput = ref(null)
const cvInput = ref(null)
const profileInput = ref(null)

const form = useForm({

  _method: 'PUT',
  first_name: '',
  last_name: '',
  languages: [],
  specialisation_ids: [],
  expertise_ids: [],
  price_per_session: 0,
  phone: '',
  country_code: '',
  is_approved: false,
  bio: '',
  gender: '',
  country: '',
  city: '',
  address: '',
  date_of_birth: '',
  profile_image_url: '',
  cv: '',
  profile_image: null,
  diploma_files: [],
  
  cv_file: null,
})

const cities = computed(() => {
  if (!form.country) return []
  return getCitiesByCountryName(form.country).map(c => c.name)
})

const dialCodes = computed(() => countriesList.value.map(c => c.dialCode).filter(Boolean))

const imagePreview = computed(() => {
  if (form.profile_image) return URL.createObjectURL(form.profile_image)
  return form.profile_image_url || props.psychologist?.profile_image_url || ''
})

const fileNameFromUrl = (url) => {
  if (!url) return ''
  try { return String(url).split('/').pop() || '' } catch { return '' }
}

const diplomaLabel = computed(() => {
  if (Array.isArray(form.diploma_files) && form.diploma_files.length) {
    if (form.diploma_files.length === 1) return form.diploma_files[0].name
    return `${form.diploma_files.length} files selected`
  }
  // show existing diplomas count or filename
  const existing = (props.psychologist?.diplomas || [])
  if (Array.isArray(existing) && existing.length) {
    if (existing.length === 1) return fileNameFromUrl(existing[0].file_url || existing[0])
    return `${existing.length} files attached`
  }
  return fileNameFromUrl(props.psychologist?.diploma || form.diploma) || 'Drag & drop or click'
})
// CIN field removed
const cvLabel = computed(() => form.cv_file?.name || fileNameFromUrl(props.psychologist?.cv || form.cv) || 'Drag & drop or click')

const ribFileLabel = computed(() => verificationForm.value.rib_file?.name || fileNameFromUrl(remoteVerification.value?.rib_file_url || props.psychologist?.verification_details?.rib_file_url) || 'Drag & drop or click')
const identityFileLabel = computed(() => verificationForm.value.identity_file?.name || fileNameFromUrl((remoteVerification.value?.identity_files_urls || props.psychologist?.verification_details?.identity_files_urls)?.[0]) || 'Drag & drop or click')
const diplomaFilesLabel = computed(() => {
  if (verificationForm.value.diploma_files.length > 0) {
    return verificationForm.value.diploma_files.length === 1 ? verificationForm.value.diploma_files[0].name : `${verificationForm.value.diploma_files.length} files selected`
  }
  const existing = (remoteVerification.value?.diploma_files_urls) || props.psychologist?.verification_details?.diploma_files_urls || []
  if (existing.length > 0) {
    return existing.length === 1 ? fileNameFromUrl(existing[0]) : `${existing.length} files attached`
  }
  return 'Drag & drop or click'
})

const daysOfWeek = [
  { value: 0, label: 'Sunday' },
  { value: 1, label: 'Monday' },
  { value: 2, label: 'Tuesday' },
  { value: 3, label: 'Wednesday' },
  { value: 4, label: 'Thursday' },
  { value: 5, label: 'Friday' },
  { value: 6, label: 'Saturday' },
]

const emptyWeeklyAvailability = () => ({
  0: [],
  1: [],
  2: [],
  3: [],
  4: [],
  5: [],
  6: [],
})

const availabilityByDay = ref(emptyWeeklyAvailability())
const availabilityErrorsByDay = ref({ 0: '', 1: '', 2: '', 3: '', 4: '', 5: '', 6: '' })
const availabilityRequiredError = ref('')

function clearAvailabilityErrors() {
  availabilityErrorsByDay.value = { 0: '', 1: '', 2: '', 3: '', 4: '', 5: '', 6: '' }
  availabilityRequiredError.value = ''
}

function normalizeTime(t) {
  if (!t) return ''
  const s = String(t)
  const m = s.match(/^(\d{2}:\d{2})/)
  return m ? m[1] : ''
}

function timeToMinutes(t) {
  if (!t || typeof t !== 'string') return null
  const m = t.match(/^(\d{2}):(\d{2})$/)
  if (!m) return null
  const hh = Number(m[1])
  const mm = Number(m[2])
  if (Number.isNaN(hh) || Number.isNaN(mm)) return null
  return hh * 60 + mm
}

function sortSlots(day) {
  availabilityByDay.value[day].sort((a, b) => {
    const am = timeToMinutes(a.start_time) ?? 0
    const bm = timeToMinutes(b.start_time) ?? 0
    return am - bm
  })
}

function validateDaySlots(day) {
  availabilityErrorsByDay.value[day] = ''
  const slots = availabilityByDay.value[day]
  if (!slots.length) return true

  const normalized = slots.map((s) => ({
    start: timeToMinutes(s.start_time),
    end: timeToMinutes(s.end_time),
  }))

  for (const s of normalized) {
    if (s.start === null || s.end === null) {
      availabilityErrorsByDay.value[day] = 'Please set start and end time for all slots.'
      return false
    }
    if (s.end <= s.start) {
      availabilityErrorsByDay.value[day] = 'End time must be after start time.'
      return false
    }
  }

  const sorted = [...normalized].sort((a, b) => a.start - b.start)
  for (let i = 1; i < sorted.length; i++) {
    const prev = sorted[i - 1]
    const cur = sorted[i]
    if (cur.start < prev.end) {
      availabilityErrorsByDay.value[day] = 'Slots overlap. Please adjust times.'
      return false
    }
  }

  return true
}

function onSlotChanged(day) {
  availabilityRequiredError.value = ''
  sortSlots(day)
  validateDaySlots(day)
}

function addSlotForDay(day) {
  availabilityErrorsByDay.value[day] = ''
  availabilityRequiredError.value = ''
  availabilityByDay.value[day].push({ start_time: '09:00', end_time: '12:00' })
  onSlotChanged(day)
}

function removeSlotForDay(day, index) {
  availabilityByDay.value[day].splice(index, 1)
  onSlotChanged(day)
}

const flattenedAvailabilities = computed(() => {
  const out = []
  for (const d of daysOfWeek) {
    for (const slot of availabilityByDay.value[d.value] || []) {
      out.push({
        day_of_week: d.value,
        start_time: slot.start_time,
        end_time: slot.end_time,
      })
    }
  }
  return out
})

function parsedAvailabilitiesByDay(availabilities) {
  const byDay = emptyWeeklyAvailability()
  for (const a of availabilities || []) {
    const day = Number(a?.day_of_week)
    if (Number.isNaN(day) || day < 0 || day > 6) continue
    byDay[day].push({
      start_time: normalizeTime(a?.start_time),
      end_time: normalizeTime(a?.end_time),
    })
  }
  for (const d of daysOfWeek) {
    byDay[d.value].sort((a, b) => {
      const am = timeToMinutes(a.start_time) ?? 0
      const bm = timeToMinutes(b.start_time) ?? 0
      return am - bm
    })
  }
  return byDay
}

function syncPhoneToForm() {
  form.country_code = dialCode.value || ''
  form.phone = nationalNumber.value || ''
}

watch(countryCode, (code) => {
  const c = countriesList.value.find(x => x.isoCode === code)
  form.country = c?.name || ''
  form.city = ''
  dialCode.value = c?.dialCode || ''
  syncPhoneToForm()
})

watch([dialCode, nationalNumber], () => syncPhoneToForm())

watch(() => section.value, (sec) => {
  if (sec === 'account') {
    const u = props.psychologist?.user
    accountForm.value.name = u?.name || ''
    accountForm.value.email = u?.email || ''
    accountForm.value.password = ''
  } else if (sec === 'verification') {
    verificationErrors.value = {}
    const vd = props.psychologist?.verification_details
    if (vd) {
      remoteVerification.value = vd
      verificationForm.value = {
        rib: vd?.rib || '',
        bank_name: vd?.bank_name || '',
        bank_account_number: vd?.bank_account_number || '',
        bank_account_name: vd?.bank_account_name || '',
        rib_file: null,
        identity_type: vd?.identity_type || '',
        identity_number: vd?.identity_number || '',
        identity_file: null,
        diploma_files: [],
        verification_status: vd?.verification_status || 'pending',
        rejection_reason: vd?.rejection_reason || '',
      }
      previousVerificationStatus.value = verificationForm.value.verification_status || 'pending'
    } else if (props.psychologist?.id) {
      ;(async () => {
        try {
          const url = route('psychologist.verification.admin.show', props.psychologist.id, false)
          const res = await fetch(url, { credentials: 'include', headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
          if (!res.ok) return
          const data = await res.json()
          const v = data?.verification || null
          remoteVerification.value = v
          verificationForm.value = {
            rib: v?.rib || '',
            bank_name: v?.bank_name || '',
            bank_account_number: v?.bank_account_number || '',
            bank_account_name: v?.bank_account_name || '',
            rib_file: null,
            identity_type: v?.identity_type || '',
            identity_number: v?.identity_number || '',
            identity_file: null,
            diploma_files: [],
            verification_status: v?.verification_status || 'pending',
            rejection_reason: v?.rejection_reason || '',
          }
          previousVerificationStatus.value = verificationForm.value.verification_status || 'pending'
        } catch (e) {
          // ignore
        }
      })()
    } else {
      verificationForm.value = {
        rib: '',
        bank_name: '',
        bank_account_number: '',
        bank_account_name: '',
        rib_file: null,
        identity_type: '',
        identity_number: '',
        identity_file: null,
        diploma_files: [],
        verification_status: 'pending',
        rejection_reason: '',
      }
    }
  }
})

const previousVerificationStatus = ref('pending')

async function handleStatusChange() {
  const newStatus = verificationForm.value.verification_status || 'pending'
  // If choosing rejected, prompt for a rejection reason
  if (newStatus === 'rejected') {
    const { value: reason, isConfirmed } = await Swal.fire({
      title: 'Enter rejection reason',
      input: 'textarea',
      inputLabel: 'Reason',
      inputPlaceholder: 'Describe why this verification is rejected',
      showCancelButton: true,
      confirmButtonText: 'Reject',
      cancelButtonText: 'Cancel',
      inputValidator: (val) => {
        if (!val || !String(val).trim()) return 'Please enter a rejection reason'
        return null
      }
    })

    if (isConfirmed) {
      verificationForm.value.rejection_reason = reason
      previousVerificationStatus.value = 'rejected'
    } else {
      // revert
      verificationForm.value.verification_status = previousVerificationStatus.value || 'pending'
    }
  } else {
    // approved or pending -> clear rejection reason
    verificationForm.value.rejection_reason = ''
    previousVerificationStatus.value = newStatus
  }
}

watch(() => props.psychologist, async (p) => {
  if (!p) return
  
  section.value = 'profile'
  form.country = p.country || ''
  const found = countriesList.value.find(c => c.name === (p.country || ''))
  countryCode.value = found?.isoCode || ''
  
  form.first_name = p.first_name || ''
  form.last_name = p.last_name || ''
  form.languages = Array.isArray(p.languages) ? p.languages.map(lang => (typeof lang === 'string' ? lang.toLowerCase() : lang)) : []
  form.specialisation_ids = (p.specialisations || []).map(s => s.id)
  form.expertise_ids = (p.expertises || []).map(e => e.id)
  form.price_per_session = p.price_per_session || 0
  form.phone = p.phone || ''
  form.country_code = p.country_code || ''
  form.is_approved = !!p.is_approved
  form.bio = p.bio || ''
  form.gender = p.gender || ''
  form.address = p.address || ''
  form.date_of_birth = formatDateForInput(p.date_of_birth)
  form.profile_image_url = p.profile_image_url || ''
  // diplomas are stored in `p.diplomas` (psychologist_diplomas relation)
  // CIN removed
  form.cv = p.cv || ''

  availabilityByDay.value = parsedAvailabilitiesByDay(p.availabilities)
  clearAvailabilityErrors()

  const parsed = splitInternationalPhoneNumber(p.phone || '', dialCodes.value)
  dialCode.value = parsed.dialCode || (found?.dialCode || '')
  nationalNumber.value = parsed.nationalNumber || ''
  syncPhoneToForm()
  
  await nextTick()
  form.city = p.city || ''
}, { immediate: true })

function formatDateForInput(dateValue) {
  if (!dateValue) return ''
  const d = new Date(dateValue)
  if (isNaN(d.getTime())) return ''
  return d.toISOString().split('T')[0]
}

function onFileChange(field, e) {
  const files = e?.target?.files || null
  if (!files) return
  if (field === 'diploma_files') {
    form[field] = Array.from(files)
    return
  }
  const file = files[0] || null
  form[field] = file
}

function onDrop(field, e) {
  const files = e?.dataTransfer?.files
  if (!files || !files.length) return
  if (field === 'profile_image') {
    const file = files[0]
    if (!file.type.startsWith('image/')) return
    form[field] = file
    return
  }
  if (field === 'diploma_files') {
    const arr = Array.from(files).filter(f => f.type === 'application/pdf')
    if (!arr.length) return
    form[field] = arr
    return
  }
  const file = files[0]
  if (field === 'cv_file' && file.type !== 'application/pdf') return
  form[field] = file
}

function onFileChangeVerification(field, e) {
  const files = e?.target?.files || null
  if (!files) return
  if (field === 'diploma_files') {
    verificationForm.value[field] = Array.from(files)
    return
  }
  const file = files[0] || null
  verificationForm.value[field] = file
}

function onDropVerification(field, e) {
  const files = e?.dataTransfer?.files
  if (!files || !files.length) return
  if (field === 'diploma_files') {
    const arr = Array.from(files).filter(f => f.type === 'application/pdf' || f.type.startsWith('image/'))
    if (!arr.length) return
    verificationForm.value[field] = arr
    return
  }
  const file = files[0]
  if (!file.type.startsWith('image/') && file.type !== 'application/pdf') return
  verificationForm.value[field] = file
}

async function ensureCsrfToken() {
  const m1 = document.cookie.match(/XSRF-TOKEN=([^;]+)/)
  if (m1) return { token: decodeURIComponent(m1[1]), type: 'cookie' }

  const tokenEl = document.querySelector('meta[name="csrf-token"]')
  const metaToken = tokenEl?.getAttribute('content') || ''
  if (metaToken) return { token: metaToken, type: 'meta' }

  try {
    await fetch('/sanctum/csrf-cookie', { method: 'GET', credentials: 'include' })
  } catch {}

  const m2 = document.cookie.match(/XSRF-TOKEN=([^;]+)/)
  return m2 ? { token: decodeURIComponent(m2[1]), type: 'cookie' } : { token: '', type: 'none' }
}

async function submitProfile() {
  if (!props.psychologist) return
  
  form.clearErrors()
  generalError.value = null
  clearAvailabilityErrors()
  
  const cleanPhone = (form.phone || '').replace(/\D/g, '')
  
  saving.value = true
  try {
    if (!Array.isArray(form.specialisation_ids) || form.specialisation_ids.length < 1) {
      form.setError('specialisation_ids', 'Please select at least one specialisation')
      generalError.value = 'Please select at least one specialisation.'
      return
    }

    // Availability is required: at least one slot across the week.
    if (!flattenedAvailabilities.value.length) {
      availabilityRequiredError.value = 'Please add at least one availability slot.'
      generalError.value = 'Please add at least one availability slot.'
      return
    }

    // Validate availability across days (only validate days that have slots)
    let availabilityOk = true
    for (const d of daysOfWeek) {
      if ((availabilityByDay.value[d.value] || []).length) {
        if (!validateDaySlots(d.value)) availabilityOk = false
      }
    }
    if (!availabilityOk) {
      generalError.value = 'Please fix availability slot errors.'
      return
    }

    const csrf = await ensureCsrfToken()
    const fd = new FormData()

    if (csrf.type === 'meta' && csrf.token) fd.append('_token', csrf.token)
    fd.append('_method', 'PUT')
    fd.append('first_name', form.first_name ?? '')
    fd.append('last_name', form.last_name ?? '')
    ;(form.specialisation_ids || []).forEach((id) => {
      if (id !== null && id !== undefined && String(id) !== '') {
        fd.append('specialisation_ids[]', String(id))
      }
    })
    // Append expertises robustly (may be ids, strings, or option objects)
    if (Array.isArray(form.expertise_ids) && form.expertise_ids.length) {
      (form.expertise_ids || []).forEach((it) => {
        if (it === null || it === undefined) return
        if (typeof it === 'object') {
          const v = it.value ?? it.label ?? null
          if (v !== null && v !== undefined && String(v) !== '') fd.append('expertise_ids[]', String(v))
        } else {
          if (String(it) !== '') fd.append('expertise_ids[]', String(it))
        }
      })
    } else {
      // If the user cleared all expertises, include an explicit empty array marker
      // so the backend knows to sync to an empty set (remove existing pivot relations).
      fd.append('expertise_ids', JSON.stringify([]))
    }
    ;(form.languages || []).forEach((lang) => {
      if (lang !== null && lang !== undefined && String(lang) !== '') {
        fd.append('languages[]', String(lang))
      }
    })
    fd.append('price_per_session', String(form.price_per_session ?? 0))
    fd.append('phone', cleanPhone)
    fd.append('country_code', form.country_code ?? '')
    fd.append('is_approved', form.is_approved ? '1' : '0')
    fd.append('bio', form.bio ?? '')
    fd.append('gender', form.gender ?? '')
    fd.append('country', form.country ?? '')
    fd.append('city', form.city ?? '')
    fd.append('address', form.address ?? '')
    fd.append('date_of_birth', form.date_of_birth ?? '')
    fd.append('profile_image_url', form.profile_image_url ?? '')
    fd.append('diploma', form.diploma ?? '')
    fd.append('cv', form.cv ?? '')
    if (form.profile_image) fd.append('profile_image', form.profile_image)
    if (Array.isArray(form.diploma_files) && form.diploma_files.length) {
      form.diploma_files.forEach((f) => fd.append('diploma_files[]', f))
    }
    
    if (form.cv_file) fd.append('cv_file', form.cv_file)

    fd.append('availabilities', JSON.stringify(flattenedAvailabilities.value))

    const headers = {
      'X-Requested-With': 'XMLHttpRequest',
      'Accept': 'application/json',
    }
    
    if (csrf.type === 'meta' && csrf.token) {
      headers['X-CSRF-TOKEN'] = csrf.token
    } else if (csrf.type === 'cookie' && csrf.token) {
      headers['X-XSRF-TOKEN'] = csrf.token
    }
    
    const url = route('psychologist-profiles.update', props.psychologist.id, false)
    const res = await fetch(url, {
      method: 'POST',
      headers: headers,
      body: fd,
      credentials: 'include',
    })

    if (!res.ok) {
      const fallback = 'Failed to save profile'
      const raw = await res.text().catch(() => '')
      console.log('Response status:', res.status, 'Response text:', raw)
      let message = fallback
      if (raw) {
        try {
          const data = JSON.parse(raw)
          if (data?.errors) {
            Object.keys(data.errors).forEach(key => {
              let fieldKey = key
              if (key.startsWith('diploma_files.')) {
                fieldKey = 'diploma_files'
              }
              form.setError(fieldKey, data.errors[key][0])
            })
            return
          }
          message = data?.message || fallback
          
          if (message.includes('Integrity constraint violation')) {
            const columnMatch = message.match(/Column '(\w+)' cannot be null/i)
            if (columnMatch) {
              const fieldName = columnMatch[1].replace(/_/g, ' ')
              message = `${fieldName.charAt(0).toUpperCase() + fieldName.slice(1)} is required`
            } else {
              message = 'Required fields are missing. Please fill in all required fields.'
            }
          }
          
          if (message.includes('SQLSTATE') || message.includes('SQL:')) {
            const lines = message.split('\n')
            message = lines[0].replace(/SQLSTATE\[\w+\]:\s*/i, '').trim()
          }
        } catch {
          message = raw
        }
      }
      generalError.value = message
      return
    }

    let updated = null
    try { updated = await res.json() } catch {}
    emit('saved', { type: 'profile', profile: updated?.profile || null })
  } catch (e) {
    generalError.value = e?.message || 'Error saving profile'
  } finally {
    saving.value = false
  }
}

async function submitAccount() {
  if (!props.psychologist?.user) return
  accountSaving.value = true
  accountError.value = ''
  try {
    const payload = { name: accountForm.value.name, email: accountForm.value.email }
    if (accountForm.value.password && accountForm.value.password.trim().length > 0) {
      payload.password = accountForm.value.password
    }
    const csrf = await ensureCsrfToken()
    
    const headers = {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    }
    
    if (csrf.type === 'meta' && csrf.token) {
      headers['X-CSRF-TOKEN'] = csrf.token
    } else if (csrf.type === 'cookie' && csrf.token) {
      headers['X-XSRF-TOKEN'] = csrf.token
    }
    
    const res = await fetch(`/users/${props.psychologist.user.id}`, {
      method: 'PATCH',
      headers: headers,
      body: JSON.stringify(payload),
      credentials: 'include',
    })
    if (!res.ok) {
      let msg = 'Failed to update account'
      try {
        const data = await res.json()
        if (data?.message) msg = data.message
      } catch {}
      if (res.status === 419) msg = 'Session expired. Please refresh the page.'
      accountError.value = msg
      return
    }

    emit('saved', {
      type: 'account',
      user: {
        id: props.psychologist.user.id,
        name: payload.name,
        email: payload.email,
      },
    })
  } finally {
    accountSaving.value = false
  }
}

async function toggleActivation() {
  if (!props.psychologist?.user) return
  try {
    const url = props.psychologist.user.is_active 
      ? `/users/${props.psychologist.user.id}/deactivate` 
      : `/users/${props.psychologist.user.id}/activate`
    const csrf = await ensureCsrfToken()

    const headers = { 'X-Requested-With': 'XMLHttpRequest' }

    if (csrf.type === 'meta' && csrf.token) {
      headers['X-CSRF-TOKEN'] = csrf.token
    } else if (csrf.type === 'cookie' && csrf.token) {
      headers['X-XSRF-TOKEN'] = csrf.token
    }

    await fetch(url, {
      method: 'PATCH',
      headers,
      credentials: 'include',
    })

    emit('saved', {
      type: 'account',
      user: {
        id: props.psychologist.user.id,
        is_active: !props.psychologist.user.is_active,
      },
    })
  } catch {}
}

async function submitVerification() {
  if (!props.psychologist) return
  verificationSaving.value = true
  verificationError.value = ''
  verificationErrors.value = {}
  try {
    // If creating a new verification (no existing verification details), require a diploma
    const creatingVerification = !remoteVerification.value && !props.psychologist?.verification_details
    if (creatingVerification) {
      const existingDiplomas = (remoteVerification.value?.diploma_files_urls) || (props.psychologist?.verification_details?.diploma_files_urls) || (props.psychologist?.diplomas || [])
      const existingCount = Array.isArray(existingDiplomas) ? existingDiplomas.length : 0
      const hasUploaded = Array.isArray(verificationForm.value.diploma_files) && verificationForm.value.diploma_files.length > 0
      if (existingCount === 0 && !hasUploaded) {
        verificationErrors.value.diploma_files = 'Please upload at least one diploma copy.'
        verificationSaving.value = false
        return
      }
    }
    const csrf = await ensureCsrfToken()
    const fd = new FormData()

    if (csrf.type === 'meta' && csrf.token) fd.append('_token', csrf.token)
    fd.append('_method', 'PUT')

    // Add form data
    Object.keys(verificationForm.value).forEach(key => {
      const value = verificationForm.value[key]
      if (key === 'diploma_files' && Array.isArray(value)) {
        value.forEach(file => fd.append('diploma_files[]', file))
      } else if (value !== null && value !== undefined && value !== '') {
        fd.append(key, value)
      }
    })

    const headers = {
      'X-Requested-With': 'XMLHttpRequest',
      'Accept': 'application/json',
    }
    
    if (csrf.type === 'meta' && csrf.token) {
      headers['X-CSRF-TOKEN'] = csrf.token
    } else if (csrf.type === 'cookie' && csrf.token) {
      headers['X-XSRF-TOKEN'] = csrf.token
    }
    
    const url = route('psychologist.verification.admin.update', props.psychologist.id, false)
    const res = await fetch(url, {
      method: 'POST',
      headers: headers,
      body: fd,
      credentials: 'include',
    })

    if (!res.ok) {
      const fallback = 'Failed to save verification details'
      const raw = await res.text().catch(() => '')
      let message = fallback
      verificationErrors.value = {}
      if (raw) {
        try {
          const data = JSON.parse(raw)
          if (data?.errors) {
            Object.keys(data.errors).forEach((key) => {
              // normalize dotted array keys (diploma_files.0 -> diploma_files)
              let baseKey = String(key).replace(/\.\d+/g, '')
              if (baseKey.startsWith('diploma_files')) baseKey = 'diploma_files'
              // map server file-url keys to our input keys
              if (baseKey === 'rib_file_url') baseKey = 'rib_file'
              if (baseKey === 'identity_file_url' || baseKey === 'identity_files_urls') baseKey = 'identity_file'
              verificationErrors.value[baseKey] = data.errors[key][0]
            })
            return
          }
          message = data?.message || fallback
        } catch {
          message = raw
        }
      }
      verificationError.value = message
      return
    }

    let updated = null
    try { updated = await res.json() } catch {}
    emit('saved', { type: 'verification', verification: updated?.verification || null })
  } catch (e) {
    verificationError.value = e?.message || 'Error saving verification details'
  } finally {
    verificationSaving.value = false
  }
}
</script>

<style scoped>
.styled-scrollbar {
  scrollbar-width: thin;
  scrollbar-color: rgb(89 151 172 / var(--tw-bg-opacity, 1)) rgba(229, 231, 235, 1);
}
.styled-scrollbar::-webkit-scrollbar {
  width: 10px;
  height: 10px;
}
.styled-scrollbar::-webkit-scrollbar-track {
  background: rgba(241, 245, 249, 1);
  border-radius: 9999px;
}
.styled-scrollbar::-webkit-scrollbar-thumb {
  background: rgb(89 151 172 / var(--tw-bg-opacity, 1));
  border-radius: 9999px;
  border: 2px solid #ffffff;
}
.styled-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgb(89 151 172 / 0.85);
}
</style>
