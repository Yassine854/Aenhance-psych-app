<template>
  <div class="p-6 space-y-6">
    <header class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900">Psychologists</h1>
        <p class="text-sm text-gray-600">Manage profiles: list, view, add, and delete.</p>
      </div>
      <div class="flex items-center gap-3 w-full md:w-auto">
        <div class="relative flex-1">
          <input v-model="searchQuery" type="text" placeholder="Search by ID, name, specialization, email..." class="w-full rounded-lg border-gray-300 pl-10 pr-3 py-2"/>
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.9 14.32a8 8 0 111.414-1.414l4.387 4.387a1 1 0 01-1.414 1.414l-4.387-4.387zM14 8a6 6 0 11-12 0 6 6 0 0112 0z" clip-rule="evenodd"/></svg>
        </div>
        <button @click="openCreate()" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg shadow hover:bg-indigo-700">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"/></svg>
          <span>New Psychologist</span>
        </button>
      </div>
    </header>
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Specialization</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Approved</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="p in filtered" :key="p.id" class="hover:bg-gray-50">
                <td class="px-4 py-3 text-sm text-gray-700">#{{ p.id }}</td>
                <td class="px-4 py-3">
                  <div class="flex items-center gap-3">
                    <img v-if="p.profile_image_url" :src="p.profile_image_url" class="h-9 w-9 rounded-full object-cover" />
                    <div>
                      <div class="text-sm font-medium text-gray-900">{{ p.first_name }} {{ p.last_name }}</div>
                      <div class="text-xs text-gray-500">{{ p.gender || '-' }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-4 py-3 text-sm text-gray-700">{{ p.specialization || '-' }}</td>
                <td class="px-4 py-3 text-sm text-gray-700">{{ formatCurrency(p.price_per_session) }}</td>
                <td class="px-4 py-3">
                  <span :class="p.is_approved ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'" class="inline-flex items-center px-2 py-1 rounded text-xs font-medium">
                    {{ p.is_approved ? 'Approved' : 'Pending' }}
                  </span>
                </td>
                <td class="px-4 py-3 text-right">
                  <div class="inline-flex items-center gap-2">
                    <button @click="openShow(p)" class="px-3 py-1.5 text-indigo-700 bg-indigo-50 rounded hover:bg-indigo-100 text-sm">View</button>
                    <button @click="openEdit(p)" class="px-3 py-1.5 text-blue-700 bg-blue-50 rounded hover:bg-blue-100 text-sm">Edit</button>
                    <button @click="confirmDelete(p)" class="px-3 py-1.5 text-red-700 bg-red-50 rounded hover:bg-red-100 text-sm">Delete</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="flex items-center justify-between px-4 py-3 border-t border-gray-200">
          <div class="text-sm text-gray-600">Showing {{ profiles.from }}-{{ profiles.to }} of {{ profiles.total }}</div>
          <div class="flex items-center gap-2">
            <Link v-for="(link, i) in profiles.links" :key="i" :href="link.url || '#'" :class="linkClasses(link)" preserve-scroll>
              <span v-html="link.label"></span>
            </Link>
          </div>
        </div>
      </div>

      <!-- Create Modal -->
      <div v-if="modal==='create'" class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/50" @click="closeModal"></div>
        <div class="relative bg-white w-full max-w-3xl rounded-xl shadow-lg p-6">
          <h2 class="text-xl font-semibold mb-4">Add Psychologist</h2>
          <div class="mb-4 flex items-center gap-4">
            <label class="flex items-center gap-2"><input type="radio" v-model="createMode" value="link"/> <span>Link existing user</span></label>
            <label class="flex items-center gap-2"><input type="radio" v-model="createMode" value="new"/> <span>Create new user</span></label>
          </div>

          <div v-if="createMode==='new'" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
              <label class="text-sm font-medium text-gray-700">Account name</label>
              <input v-model="newUser.name" class="mt-1 block w-full rounded-md border-gray-300" />
            </div>
            <div>
              <label class="text-sm font-medium text-gray-700">Email</label>
              <input v-model="newUser.email" type="email" class="mt-1 block w-full rounded-md border-gray-300" />
            </div>
            <div>
              <label class="text-sm font-medium text-gray-700">Password</label>
              <input v-model="newUser.password" type="password" class="mt-1 block w-full rounded-md border-gray-300" />
            </div>
            <div>
              <label class="text-sm font-medium text-gray-700">Role</label>
              <input value="PSYCHOLOGIST" disabled class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100" />
            </div>
          </div>

          <form @submit.prevent="submitCreate" class="space-y-4">
            <div v-if="createMode==='link'">
              <label class="text-sm font-medium text-gray-700">User (existing)</label>
              <select v-model="createForm.user_id" class="mt-1 block w-full rounded-md border-gray-300">
                <option value="">Select user</option>
                <option v-for="u in psychologistUsers" :key="u.id" :value="u.id">{{ u.name }} ({{ u.email }})</option>
              </select>
            </div>

            <!-- Row 1: First, Last, Specialization -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
              <div>
                <label class="text-sm font-medium text-gray-700">First name</label>
                <input v-model="createForm.first_name" class="mt-1 block w-full rounded-md border-gray-300" />
              </div>
              <div>
                <label class="text-sm font-medium text-gray-700">Last name</label>
                <input v-model="createForm.last_name" class="mt-1 block w-full rounded-md border-gray-300" />
              </div>
              <div>
                <label class="text-sm font-medium text-gray-700">Specialization</label>
                <input v-model="createForm.specialization" class="mt-1 block w-full rounded-md border-gray-300" />
              </div>
            </div>

            <!-- Row 2: Gender (4th), Date of Birth (5th), Price (6th) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
              <div>
                <label class="text-sm font-medium text-gray-700">Gender</label>
                <select v-model="createForm.gender" class="mt-1 block w-full rounded-md border-gray-300">
                  <option value="">Select gender</option>
                  <option value="MALE">Male</option>
                  <option value="FEMALE">Female</option>
                  <option value="OTHER">Other</option>
                </select>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-700">Date of birth</label>
                <input type="date" v-model="createForm.date_of_birth" class="mt-1 block w-full rounded-md border-gray-300" />
              </div>
              <div>
                <label class="text-sm font-medium text-gray-700">Price per session (DT)</label>
                <input type="number" step="0.01" v-model="createForm.price_per_session" class="mt-1 block w-full rounded-md border-gray-300" />
              </div>
            </div>

            <!-- Row 3: Country, City, Phone -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
              <div>
                <label class="text-sm font-medium text-gray-700">Country</label>
                <select v-model="createCountryCode" class="mt-1 block w-full rounded-md border-gray-300">
                  <option value="">Select country</option>
                  <option v-for="c in countriesList" :key="c.isoCode" :value="c.isoCode">{{ c.name }}</option>
                </select>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-700">City</label>
                <select v-model="createForm.city" class="mt-1 block w-full rounded-md border-gray-300" :disabled="citiesLoading">
                  <option value="">{{ citiesLoading ? 'Loading cities…' : 'Select city' }}</option>
                  <option v-for="ct in createCities" :key="ct" :value="ct">{{ ct }}</option>
                </select>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-700">Phone</label>
                <div class="mt-1 flex">
                  <input
                    v-model="createDialCode"
                    readonly
                    class="w-24 rounded-l-md border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-700"
                    placeholder="+___"
                  />
                  <input
                    v-model="createNationalNumber"
                    inputmode="tel"
                    class="block w-full rounded-r-md border border-l-0 border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-[rgb(89,151,172)]"
                    placeholder="Enter phone number"
                  />
                </div>
              </div>
            </div>

            <!-- Last row: Address and Approved -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
              <div class="md:col-span-2">
                <label class="text-sm font-medium text-gray-700">Address</label>
                <input v-model="createForm.address" class="mt-1 block w-full rounded-md border-gray-300" />
              </div>
              <div>
                <label class="text-sm font-medium text-gray-700">Approved</label>
                <select v-model="createForm.is_approved" class="mt-1 block w-full rounded-md border-gray-300">
                  <option :value="true">Yes</option>
                  <option :value="false">No</option>
                </select>
              </div>
            </div>

            <div>
              <label class="text-sm font-medium text-gray-700">Bio</label>
              <textarea v-model="createForm.bio" class="mt-1 block w-full rounded-md border-gray-300 h-24"></textarea>
            </div>

            <div class="flex items-center gap-3">
              <button :disabled="creating" class="px-4 py-2 bg-indigo-600 text-white rounded-lg shadow hover:bg-indigo-700">Create</button>
              <button type="button" @click="closeModal" class="text-sm text-gray-600">Cancel</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Show Modal -->
      <div v-if="modal==='show' && selected" class="fixed inset-0 z-[1000] flex items-center justify-center">
        <div class="absolute inset-0 bg-black/50" @click="closeModal"></div>
        <div class="relative bg-white w-full max-w-4xl rounded-xl shadow-lg p-6 max-h-[85vh] overflow-y-auto styled-scrollbar">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold">Psychologist #{{ selected.id }}</h2>
            <button @click="closeModal" class="text-gray-500 hover:text-gray-700">✕</button>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-1 flex flex-col items-center">
              <img v-if="selected.profile_image_url" :src="selected.profile_image_url" class="h-32 w-32 rounded-full object-cover" />
              <div v-else class="h-32 w-32 rounded-full bg-gray-100 flex items-center justify-center text-gray-400">No photo</div>
              <div class="mt-3 text-center">
                <div class="font-semibold text-gray-900">{{ selected.first_name }} {{ selected.last_name }}</div>
                <div class="text-sm text-gray-600">{{ selected.specialization || '-' }}</div>
              </div>
            </div>
            <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
              <InfoRow label="Email" :value="selected.user?.email" />
              <InfoRow label="Price per session" :value="formatCurrency(selected.price_per_session)" />
              <InfoRow label="Gender" :value="selected.gender" />
              <InfoRow label="Country" :value="selected.country" />
              <InfoRow label="City" :value="selected.city" />
              <InfoRow label="Phone" :value="selected.phone" />
              <InfoRow label="Address" :value="selected.address" />
              <InfoRow label="Approved" :value="selected.is_approved ? 'Yes' : 'No'" />
              <InfoRow label="Date of birth" :value="formatDate(selected.date_of_birth)" />
            </div>
          </div>
          <div class="mt-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Bio</h3>
            <p class="text-gray-700 whitespace-pre-line">{{ selected.bio || '—' }}</p>
          </div>
          <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <AttachmentCard v-if="selected.diploma" title="Diploma (PDF)" :href="selected.diploma" />
            <AttachmentCard v-if="selected.cin" title="CIN (PDF)" :href="selected.cin" />
          </div>
          <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <InfoRow label="Account name" :value="selected.user?.name" />
            <InfoRow label="Account active" :value="selected.user?.is_active ? 'Yes' : 'No'" />
          </div>
        </div>
      </div>

      <!-- Edit Modal (Cool style with tabs) -->
      <div v-if="modal==='edit' && selected" class="fixed inset-0 z-[1000] flex items-center justify-center">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal"></div>
        <div class="relative w-full max-w-7xl rounded-2xl shadow-2xl overflow-hidden">
          <!-- Gradient header with avatar -->
          <div class="bg-gradient-to-r from-[rgb(141,61,79)] to-[rgb(89,151,172)] p-6">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-4">
                <img v-if="selected.profile_image_url" :src="selected.profile_image_url" class="h-14 w-14 rounded-full ring-2 ring-white/70 object-cover" />
                <div v-else class="h-14 w-14 rounded-full bg-white/20 flex items-center justify-center text-white">No</div>
                <div class="text-white">
                  <div class="text-xl font-semibold">{{ selected.first_name }} {{ selected.last_name }}</div>
                  <div class="text-sm opacity-90">Psychologist #{{ selected.id }} • {{ selected.specialization || '—' }}</div>
                </div>
              </div>
              <button @click="closeModal" class="text-white/90 hover:text-white text-2xl leading-none">✕</button>
            </div>
            <!-- Tabs -->
            <div class="mt-4 flex items-center gap-2">
              <button @click="editSection='profile'" :class="editSection==='profile' ? 'bg-white text-gray-900 shadow' : 'bg-white/20 text-white hover:bg-white/30'" class="px-4 py-2 rounded-lg transition">
                Profile Details
              </button>
              <button @click="editSection='account'" :class="editSection==='account' ? 'bg-white text-gray-900 shadow' : 'bg-white/20 text-white hover:bg-white/30'" class="px-4 py-2 rounded-lg transition">
                Account
              </button>
            </div>
          </div>
          <!-- Content -->
          <div class="bg-white p-6 max-h-[70vh] overflow-y-auto styled-scrollbar">
            <!-- Profile section -->
            <form v-if="editSection==='profile'" @submit.prevent="submitEdit" class="space-y-4">
              <!-- Row 1: First, Last, Specialization -->
              <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div>
                  <label class="text-sm font-medium text-gray-700">First name</label>
                  <input v-model="editForm.first_name" class="mt-1 block w-full rounded-md border-gray-300 focus:ring-2 focus:ring-[rgb(89,151,172)]" />
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-700">Last name</label>
                  <input v-model="editForm.last_name" class="mt-1 block w-full rounded-md border-gray-300 focus:ring-2 focus:ring-[rgb(89,151,172)]" />
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-700">Specialization</label>
                  <input v-model="editForm.specialization" class="mt-1 block w-full rounded-md border-gray-300 focus:ring-2 focus:ring-[rgb(89,151,172)]" />
                </div>
              </div>

              <!-- Row 2: Gender (4th), Date of Birth (5th), Price (6th) -->
              <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div>
                  <label class="text-sm font-medium text-gray-700">Gender</label>
                  <select v-model="editForm.gender" class="mt-1 block w-full rounded-md border-gray-300 focus:ring-2 focus:ring-[rgb(89,151,172)]">
                    <option value="">Select gender</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                  </select>
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-700">Date of birth</label>
                  <input type="date" v-model="editForm.date_of_birth" class="mt-1 block w-full rounded-md border-gray-300 focus:ring-2 focus:ring-[rgb(89,151,172)]" />
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-700">Price per session (DT)</label>
                  <input type="number" step="0.01" v-model="editForm.price_per_session" class="mt-1 block w-full rounded-md border-gray-300 focus:ring-2 focus:ring-[rgb(89,151,172)]" />
                </div>
              </div>

              <!-- Row 3: Country, City, Phone -->
              <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div>
                  <label class="text-sm font-medium text-gray-700">Country</label>
                  <select v-model="editCountryCode" class="mt-1 block w-full rounded-md border-gray-300 focus:ring-2 focus:ring-[rgb(89,151,172)]">
                    <option value="">Select country</option>
                    <option v-for="c in countriesList" :key="c.isoCode" :value="c.isoCode">{{ c.name }}</option>
                  </select>
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-700">City</label>
                  <select v-model="editForm.city" class="mt-1 block w-full rounded-md border-gray-300 focus:ring-2 focus:ring-[rgb(89,151,172)]" :disabled="citiesLoading">
                    <option value="">{{ citiesLoading ? 'Loading cities…' : 'Select city' }}</option>
                    <option v-for="ct in editCities" :key="ct" :value="ct">{{ ct }}</option>
                  </select>
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-700">Phone</label>
                  <div class="mt-1 flex">
                    <input
                      v-model="editDialCode"
                      readonly
                      class="w-24 rounded-l-md border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-700"
                      placeholder="+___"
                    />
                    <input
                      v-model="editNationalNumber"
                      inputmode="tel"
                      class="block w-full rounded-r-md border border-l-0 border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-[rgb(89,151,172)]"
                      placeholder="Enter phone number"
                    />
                  </div>
                </div>
              </div>

              <!-- Last row: Address and Approved -->
              <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div class="md:col-span-2">
                  <label class="text-sm font-medium text-gray-700">Address</label>
                  <input v-model="editForm.address" class="mt-1 block w-full rounded-md border-gray-300 focus:ring-2 focus:ring-[rgb(89,151,172)]" />
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-700">Approved</label>
                  <select v-model="editForm.is_approved" class="mt-1 block w-full rounded-md border-gray-300 focus:ring-2 focus:ring-[rgb(89,151,172)]">
                    <option :value="true">Yes</option>
                    <option :value="false">No</option>
                  </select>
                </div>
              </div>

              <!-- Uploads: CIN, Diploma, Profile image -->
              <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <!-- CIN Dropzone -->
                <div>
                  <label class="text-sm font-medium text-gray-700">CIN (PDF)</label>
                  <div
                    class="mt-1 group relative flex flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 p-4 text-center cursor-pointer transition hover:bg-gray-100 hover:border-[rgb(89,151,172)]"
                    @click="() => editCinInput?.click()"
                    @dragover.prevent
                    @drop.prevent="onEditDrop('cin_file', $event)"
                  >
                    <input ref="editCinInput" type="file" accept="application/pdf" class="hidden" @change="onEditFileChange('cin_file', $event)" />
                    <div class="flex items-center gap-2 text-gray-600">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="currentColor"><path d="M6 2h9l5 5v13a2 2 0 01-2 2H6a2 2 0 01-2-2V4a2 2 0 012-2zm8 1.5V8h4.5L14 3.5z"/></svg>
                      <span class="text-xs">{{ editCinLabel }}</span>
                    </div>
                  </div>
                </div>
                <!-- Diploma Dropzone -->
                <div>
                  <label class="text-sm font-medium text-gray-700">Diploma (PDF)</label>
                  <div
                    class="mt-1 group relative flex flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 p-4 text-center cursor-pointer transition hover:bg-gray-100 hover:border-[rgb(89,151,172)]"
                    @click="() => editDiplomaInput?.click()"
                    @dragover.prevent
                    @drop.prevent="onEditDrop('diploma_file', $event)"
                  >
                    <input ref="editDiplomaInput" type="file" accept="application/pdf" class="hidden" @change="onEditFileChange('diploma_file', $event)" />
                    <div class="flex items-center gap-2 text-gray-600">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="currentColor"><path d="M6 2h9l5 5v13a2 2 0 01-2 2H6a2 2 0 01-2-2V4a2 2 0 012-2zm8 1.5V8h4.5L14 3.5z"/></svg>
                      <span class="text-xs">{{ editDiplomaLabel }}</span>
                    </div>
                  </div>
                </div>
                <!-- Profile Image Dropzone -->
                <div>
                  <label class="text-sm font-medium text-gray-700">Profile image</label>
                  <div
                    class="mt-1 group relative flex flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 p-4 text-center cursor-pointer transition hover:bg-gray-100 hover:border-[rgb(89,151,172)]"
                    @click="() => editProfileInput?.click()"
                    @dragover.prevent
                    @drop.prevent="onEditDrop('profile_image', $event)"
                  >
                    <input ref="editProfileInput" type="file" accept="image/*" class="hidden" @change="onEditFileChange('profile_image', $event)" />
                    <div v-if="editImagePreview" class="flex flex-col items-center gap-2">
                      <img :src="editImagePreview" class="h-16 w-16 rounded-full object-cover ring-1 ring-gray-200" />
                      <span class="text-xs text-gray-600">Click to replace</span>
                    </div>
                    <div v-else class="flex flex-col items-center gap-2 text-gray-500">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 24 24" fill="currentColor"><path d="M12 5a7 7 0 100 14 7 7 0 000-14zm0 2a2.5 2.5 0 11-.001 5.001A2.5 2.5 0 0112 7zm0 10a5.5 5.5 0 01-4.686-2.5 3.5 3.5 0 016.372 0A5.5 5.5 0 0112 17z"/></svg>
                      <span class="text-xs">Drag & drop or click</span>
                    </div>
                  </div>
                </div>
              </div>

              <div>
                <label class="text-sm font-medium text-gray-700">Bio</label>
                <textarea v-model="editForm.bio" class="mt-1 block w-full rounded-md border-gray-300 h-28 focus:ring-2 focus:ring-[rgb(89,151,172)]"></textarea>
              </div>

              <div class="flex items-center gap-3">
                <button :disabled="saving" class="px-4 py-2 bg-[rgb(141,61,79)] text-white rounded-lg shadow hover:opacity-90">Save Profile</button>
                <button type="button" @click="closeModal" class="text-sm text-gray-600">Cancel</button>
              </div>
            </form>

            <!-- Account section -->
            <form v-else @submit.prevent="submitAccount" class="space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="text-sm font-medium text-gray-700">Account name</label>
                  <input v-model="editAccount.name" class="mt-1 block w-full rounded-md border-gray-300 focus:ring-2 focus:ring-[rgb(89,151,172)]" />
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-700">Email</label>
                  <input v-model="editAccount.email" type="email" class="mt-1 block w-full rounded-md border-gray-300 focus:ring-2 focus:ring-[rgb(89,151,172)]" />
                </div>
                <div class="md:col-span-2">
                  <label class="text-sm font-medium text-gray-700">Password</label>
                  <input v-model="editAccount.password" type="password" placeholder="Leave empty to keep current" class="mt-1 block w-full rounded-md border-gray-300 focus:ring-2 focus:ring-[rgb(89,151,172)]" />
                  <p class="mt-2 text-xs text-gray-500">If left blank, the password remains unchanged.</p>
                </div>
              </div>

              <p v-if="!selected.user" class="text-sm text-yellow-700 bg-yellow-50 border border-yellow-200 rounded px-3 py-2">Link a user to enable account editing.</p>
              <p v-if="accountError" class="text-sm text-red-700 bg-red-50 border border-red-200 rounded px-3 py-2">{{ accountError }}</p>

              <div class="flex items-center gap-3">
                <button type="submit" :disabled="accountSaving || !selected.user" class="px-4 py-2 bg-[rgb(89,151,172)] text-white rounded-lg shadow hover:opacity-90">Save Account</button>
                <button v-if="selected.user" type="button" @click="toggleActivation(selected.user)" class="px-4 py-2 rounded-lg" :class="selected.user.is_active ? 'bg-yellow-600 text-white hover:bg-yellow-700' : 'bg-green-600 text-white hover:bg-green-700'">
                  {{ selected.user.is_active ? 'Deactivate' : 'Activate' }}
                </button>
                <button type="button" @click="closeModal" class="text-sm text-gray-600">Cancel</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import { Inertia } from '@inertiajs/inertia'
import { ref, computed, onMounted, watch, nextTick } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { getCountries, getCitiesByCountryName, splitInternationalPhoneNumber } from '@/utils/geoData'

const props = defineProps({ profiles: Object })

const searchQuery = ref('')
const filtered = computed(() => {
  const q = searchQuery.value.trim().toLowerCase()
  if (!q) return props.profiles.data || []
  return (props.profiles.data || []).filter(p => {
    const fields = [
      String(p.id),
      p.first_name || '',
      p.last_name || '',
      p.specialization || '',
      p.user?.email || '',
    ].map(s => String(s).toLowerCase())
    return fields.some(f => f.includes(q))
  })
})

function formatCurrency(value) {
  if (value == null) return '-'
  const n = Number(value)
  return new Intl.NumberFormat(undefined, { style: 'currency', currency: 'TND', minimumFractionDigits: 2 }).format(n)
}

function formatDate(value) {
  if (!value) return '-' 
  try { return new Date(value).toLocaleDateString() } catch { return '-' }
}

function linkClasses(link) {
  const base = 'px-3 py-1.5 rounded text-sm'
  if (!link.url) return base + ' text-gray-400 bg-gray-100 cursor-not-allowed'
  return (link.active ? base + ' bg-indigo-600 text-white' : base + ' bg-gray-50 text-gray-700 hover:bg-gray-100')
}

// Modals state
const modal = ref(null) // 'create' | 'show' | 'edit' | null
const selected = ref(null)
const editSection = ref('profile') // 'profile' | 'account'

// Country/City/Phone helpers
const countriesList = ref(getCountries())
const editCountryCode = ref('')
const createCountryCode = ref('')

const editCities = computed(() => {
  if (!editForm.country) return []
  return getCitiesByCountryName(editForm.country).map(c => c.name)
})

const createCities = computed(() => {
  if (!createForm.country) return []
  return getCitiesByCountryName(createForm.country).map(c => c.name)
})

const dialCodes = computed(() => countriesList.value.map(c => c.dialCode).filter(Boolean))
const editDialCode = ref('')
const editNationalNumber = ref('')
const createDialCode = ref('')
const createNationalNumber = ref('')

function syncPhoneToForm(kind) {
  if (kind === 'edit') {
    editForm.country_code = editDialCode.value || ''
    editForm.phone = `${editDialCode.value || ''}${editNationalNumber.value || ''}`
  } else {
    createForm.country_code = createDialCode.value || ''
    createForm.phone = `${createDialCode.value || ''}${createNationalNumber.value || ''}`
  }
}

watch(editCountryCode, (code) => {
  const c = countriesList.value.find(x => x.isoCode === code)
  editForm.country = c?.name || ''
  editForm.city = ''
  editDialCode.value = c?.dialCode || ''
  syncPhoneToForm('edit')
})
watch(createCountryCode, (code) => {
  const c = countriesList.value.find(x => x.isoCode === code)
  createForm.country = c?.name || ''
  createForm.city = ''
  createDialCode.value = c?.dialCode || ''
  syncPhoneToForm('create')
})

watch([editDialCode, editNationalNumber], () => syncPhoneToForm('edit'))
watch([createDialCode, createNationalNumber], () => syncPhoneToForm('create'))

function closeModal() {
  modal.value = null
  selected.value = null
}

function openShow(p) {
  selected.value = p
  modal.value = 'show'
}

// Edit form
const editForm = useForm({
  _method: 'PUT',
  first_name: '',
  last_name: '',
  specialization: '',
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
  diploma: '',
  cin: '',
  profile_image: null,
  diploma_file: null,
  cin_file: null,
})

async function openEdit(p) {
  selected.value = p
  editSection.value = 'profile'
  
  // Set country first so city dropdown populates correctly
  editForm.country = p.country || ''
  const found = countriesList.value.find(c => c.name === (p.country || ''))
  editCountryCode.value = found?.isoCode || ''
  
  // Now set other fields
  editForm.first_name = p.first_name || ''
  editForm.last_name = p.last_name || ''
  editForm.specialization = p.specialization || ''
  editForm.price_per_session = p.price_per_session || 0
  editForm.phone = p.phone || ''
  editForm.country_code = p.country_code || ''
  editForm.is_approved = !!p.is_approved
  editForm.bio = p.bio || ''
  editForm.gender = p.gender || ''
  editForm.address = p.address || ''
  editForm.date_of_birth = formatDateForInput(p.date_of_birth)
  editForm.profile_image_url = p.profile_image_url || ''
  editForm.diploma = p.diploma || ''
  editForm.cin = p.cin || ''

  const parsed = splitInternationalPhoneNumber(p.phone || '', dialCodes.value)
  editDialCode.value = parsed.dialCode || (found?.dialCode || '')
  editNationalNumber.value = parsed.nationalNumber || ''
  syncPhoneToForm('edit')
  
  // Set city after nextTick to ensure watch has completed
  await nextTick()
  editForm.city = p.city || ''
  
  modal.value = 'edit'
}
function onEditFileChange(field, e) {
  const file = e?.target?.files?.[0] || null
  editForm[field] = file
}
function formatDateForInput(dateValue) {
  if (!dateValue) return ''
  const d = new Date(dateValue)
  if (isNaN(d.getTime())) return ''
  return d.toISOString().split('T')[0]
}

function normalizePhone(value) {
  if (!value) return ''
  // Keep leading +, strip spaces, dashes, parentheses and other non-digits
  let v = String(value)
  // Remove everything except digits and plus
  v = v.replace(/[^\d+]/g, '')
  // Ensure only one leading plus
  v = v.replace(/^(?:\+)+/, '+')
  return v
}

function extractPhoneValue(phoneValue) {
  if (!phoneValue) return ''
  if (typeof phoneValue === 'string' || typeof phoneValue === 'number') return String(phoneValue)
  // vue3-tel-input can emit an object depending on config
  if (typeof phoneValue === 'object') {
    return (
      phoneValue.internationalNumber ||
      phoneValue.number ||
      phoneValue.formattedNumber ||
      phoneValue.nationalNumber ||
      ''
    )
  }
  return ''
}

const saving = ref(false)
async function submitEdit() {
  if (!selected.value) return
  
  // DEBUG: Log phone value before processing - BEFORE closing modal
  console.log('=== SUBMIT EDIT DEBUG ===')
  console.log('editForm.phone:', editForm.phone, 'type:', typeof editForm.phone)
  console.log('editForm.phone raw:', editForm.phone)
  console.log('isCreating:', !selected.value.id)
  const normalizedPhone = normalizePhone(extractPhoneValue(editForm.phone) ?? '')
  console.log('Phone after normalize:', normalizedPhone)
  console.log('Phone length:', normalizedPhone.length)
  console.log('========================')
  
  // Capture ID and user_id before closing, as closeModal clears selected
  const profileId = selected.value.id
  const userId = selected.value.user_id
  const isCreating = !profileId // If no profile ID, we're creating a new profile
  
  saving.value = true
  try {
    // Close immediately to avoid any empty modal flicker
    closeModal()

    if (isCreating && !normalizedPhone) {
      alert('Phone is required')
      return
    }

    // Manually submit with FormData to avoid Inertia overlay flicker
    const csrf = await ensureCsrfToken()
    const fd = new FormData()
    
    // Add CSRF token to FormData for Laravel verification
    if (csrf.token) {
      fd.append('_token', csrf.token)
    }
    
    if (!isCreating) {
      fd.append('_method', 'PUT')
    }
    
    // Add user_id for profile creation
    if (isCreating) {
      fd.append('user_id', String(userId))
    }
    
    fd.append('first_name', editForm.first_name ?? '')
    fd.append('last_name', editForm.last_name ?? '')
    fd.append('specialization', editForm.specialization ?? '')
    fd.append('price_per_session', String(editForm.price_per_session ?? 0))
    fd.append('phone', normalizedPhone)
    fd.append('country_code', editForm.country_code ?? '')
    fd.append('is_approved', editForm.is_approved ? '1' : '0')
    fd.append('bio', editForm.bio ?? '')
    fd.append('gender', editForm.gender ?? '')
    fd.append('country', editForm.country ?? '')
    fd.append('city', editForm.city ?? '')
    fd.append('address', editForm.address ?? '')
    fd.append('date_of_birth', editForm.date_of_birth ?? '')
    fd.append('profile_image_url', editForm.profile_image_url ?? '')
    fd.append('diploma', editForm.diploma ?? '')
    fd.append('cin', editForm.cin ?? '')
    if (editForm.profile_image) fd.append('profile_image', editForm.profile_image)
    if (editForm.diploma_file) fd.append('diploma_file', editForm.diploma_file)
    if (editForm.cin_file) fd.append('cin_file', editForm.cin_file)

    const url = isCreating 
      ? route('psychologist-profiles.store')
      : route('psychologist-profiles.update', profileId)
    
    // Build headers based on token type
    const headers = {
      'X-Requested-With': 'XMLHttpRequest',
      'X-Inertia': 'true',
      'Accept': 'application/json',
    }
    
    // Add appropriate CSRF header based on token source
    if (csrf.type === 'meta' && csrf.token) {
      headers['X-CSRF-TOKEN'] = csrf.token
    } else if (csrf.type === 'cookie' && csrf.token) {
      headers['X-XSRF-TOKEN'] = csrf.token
    }
    
    const res = await fetch(url, {
      method: 'POST',
      headers: headers,
      body: fd,
      credentials: 'same-origin',
    })

    // Inertia version conflict: force a full reload to the provided location
    if (res.status === 409) {
      const location = res.headers.get('x-inertia-location')
      if (location) {
        window.location.href = location
        return
      }
    }

    if (!res.ok) {
      // If failed, re-open modal with an error state (optional)
      const fallback = 'Failed to save profile'
      const raw = await res.text().catch(() => '')
      let message = fallback
      if (raw) {
        try {
          const data = JSON.parse(raw)
          message = data?.message || fallback
        } catch {
          message = raw
        }
      }
      alert(message)
      return
    }

    await Inertia.get(route('psychologist-profiles.index'), {}, { preserveScroll: true, replace: true, preserveState: false })
  } catch (e) {
    console.error('submitEdit error', e)
    alert(e?.message || 'Error saving profile')
  } finally {
    saving.value = false
  }
}
// Dropzone helpers for edit modal
const editProfileInput = ref(null)
const editDiplomaInput = ref(null)
const editCinInput = ref(null)

function onEditDrop(field, e) {
  const file = e?.dataTransfer?.files?.[0]
  if (!file) return
  if (field === 'profile_image' && !file.type.startsWith('image/')) return
  if ((field === 'diploma' || field === 'cin') && file.type !== 'application/pdf') return
  editForm[field] = file
}

const editImagePreview = computed(() => {
  if (editForm.profile_image) return URL.createObjectURL(editForm.profile_image)
  return editForm.profile_image_url || selected.value?.profile_image_url || ''
})

function fileNameFromUrl(url) {
  if (!url) return ''
  try { return String(url).split('/').pop() || '' } catch { return '' }
}

const editDiplomaLabel = computed(() => editForm.diploma_file?.name || fileNameFromUrl(selected.value?.diploma || editForm.diploma) || 'Drag & drop or click')
const editCinLabel = computed(() => editForm.cin_file?.name || fileNameFromUrl(selected.value?.cin || editForm.cin) || 'Drag & drop or click')

// Account editing
const editAccount = ref({ name: '', email: '', password: '' })
const accountSaving = ref(false)
const accountError = ref('')
onMounted(() => {
  // no-op; kept for symmetry
})
function initAccountFromSelected() {
  const u = selected.value?.user
  editAccount.value.name = u?.name || ''
  editAccount.value.email = u?.email || ''
  editAccount.value.password = ''
}
// Ensure account form is initialized when switching to account tab
watch(() => editSection.value, (sec) => {
  if (sec === 'account') initAccountFromSelected()
})
async function ensureCsrfToken() {
  // First priority: meta tag token
  const tokenEl = document.querySelector('meta[name="csrf-token"]')
  const metaToken = tokenEl?.getAttribute('content') || ''
  if (metaToken) return { token: metaToken, type: 'meta' }
  
  // Second priority: check for XSRF-TOKEN cookie
  const m1 = document.cookie.match(/XSRF-TOKEN=([^;]+)/)
  if (m1) return { token: decodeURIComponent(m1[1]), type: 'cookie' }
  
  // If no token found, fetch a fresh one
  try {
    await fetch('/sanctum/csrf-cookie', { method: 'GET', credentials: 'same-origin' })
  } catch {}
  
  const m2 = document.cookie.match(/XSRF-TOKEN=([^;]+)/)
  return m2 ? { token: decodeURIComponent(m2[1]), type: 'cookie' } : { token: '', type: 'none' }
}

async function submitAccount() {
  if (!selected.value?.user) return
  accountSaving.value = true
  accountError.value = ''
  try {
    const payload = { name: editAccount.value.name, email: editAccount.value.email }
    if (editAccount.value.password && editAccount.value.password.trim().length > 0) {
      payload.password = editAccount.value.password
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
    
    const res = await fetch(`/users/${selected.value.user.id}`, {
      method: 'PATCH',
      headers: headers,
      body: JSON.stringify(payload),
      credentials: 'same-origin',
    })
    if (!res.ok) {
      // Try to surface validation errors
      let msg = 'Failed to update account'
      try {
        const data = await res.json()
        if (data?.message) msg = data.message
      } catch {}
      if (res.status === 419) msg = 'Session expired. Please refresh the page.'
      accountError.value = msg
      return
    }
    // Update local state to reflect changes immediately
    if (selected.value?.user) {
      selected.value.user.name = editAccount.value.name
      selected.value.user.email = editAccount.value.email
    }
    // Close modal first to avoid a brief empty modal state
    closeModal()
    await Inertia.get(route('psychologist-profiles.index'), {}, { preserveScroll: true, replace: true })
  } finally {
    accountSaving.value = false
  }
}

// Delete
function confirmDelete(p) {
  if (!confirm(`Delete ${p.first_name} ${p.last_name}?`)) return
  const form = useForm({})
  form.delete(route('psychologist-profiles.destroy', p.id), { preserveScroll: true, onSuccess: () => {
    Inertia.get(route('psychologist-profiles.index'), {}, { preserveScroll: true, replace: true })
  } })
}

// Activate/Deactivate user
async function toggleActivation(user) {
  if (!user) return
  try {
    const url = user.is_active ? `/users/${user.id}/deactivate` : `/users/${user.id}/activate`
    const csrf = await ensureCsrfToken()

    const headers = {
      'X-Requested-With': 'XMLHttpRequest',
    }

    if (csrf.type === 'meta' && csrf.token) {
      headers['X-CSRF-TOKEN'] = csrf.token
    } else if (csrf.type === 'cookie' && csrf.token) {
      headers['X-XSRF-TOKEN'] = csrf.token
    }

    await fetch(url, {
      method: 'PATCH',
      headers,
      credentials: 'same-origin',
    })

    // Close modal first to avoid a brief empty modal state
    closeModal()
    await Inertia.get(route('psychologist-profiles.index'), {}, { preserveScroll: true, replace: true })
  } catch {}
}

// Create modal
const createMode = ref('link') // 'link' | 'new'
const psychologistUsers = ref([])
const creating = ref(false)
const createForm = useForm({
  user_id: '',
  first_name: '',
  last_name: '',
  specialization: '',
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
  diploma: '',
  cin: '',
})
const newUser = ref({ name: '', email: '', password: '' })

function openCreate() {
  modal.value = 'create'
}

onMounted(async () => {
  try {
    const res = await fetch('/users')
    const all = await res.json()
    psychologistUsers.value = (all || []).filter(u => u.role === 'PSYCHOLOGIST')
  } catch {}
})

async function submitCreate() {
  creating.value = true
  try {
    let userId = createForm.user_id
    if (createMode.value === 'new') {
      const res = await fetch('/users', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ name: newUser.value.name, email: newUser.value.email, password: newUser.value.password, role: 'PSYCHOLOGIST' })
      })
      const created = await res.json()
      userId = created.id
    }
    const payload = { ...createForm.data(), user_id: userId }
    payload.phone = normalizePhone(payload.phone)
    const form = useForm(payload)
    await form.post(route('psychologist-profiles.store'), { preserveScroll: true })
    // Close modal first to avoid a brief empty modal state
    closeModal()
    await Inertia.get(route('psychologist-profiles.index'), {}, { preserveScroll: true, replace: true })
  } finally {
    creating.value = false
  }
}
</script>

<script>
export default {
  layout: AdminLayout,
  name: 'Admin/Psychologist/Index'
}
</script>

<style scoped>
/* Stylish scrollbar for modals */
.styled-scrollbar {
  /* Firefox */
  scrollbar-width: thin;
  scrollbar-color: rgb(89 151 172 / var(--tw-bg-opacity, 1)) rgba(229, 231, 235, 1);
}
.styled-scrollbar::-webkit-scrollbar {
  width: 10px;
  height: 10px;
}
.styled-scrollbar::-webkit-scrollbar-track {
  background: rgba(241, 245, 249, 1); /* slate-100 */
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
