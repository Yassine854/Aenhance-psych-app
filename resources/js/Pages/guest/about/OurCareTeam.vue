<script setup>
import { Head, Link } from "@inertiajs/vue3";
import { computed, onMounted } from "vue";
import { useI18n } from "vue-i18n";
import Navbar from "@/Components/Navbar.vue";
import Footer from "@/Components/Footer.vue";
import { resolveStorageUrl } from "@/utils/storage";

const props = defineProps({
	canLogin: { type: Boolean },
	canRegister: { type: Boolean },
	authUser: { type: Object },
	profiles: { type: Array, default: () => [] },
});

const { t, locale } = useI18n();

function setLang(lang) {
	locale.value = lang;
	localStorage.setItem("locale", lang);

	if (lang === "ar") {
		document.documentElement.setAttribute("dir", "rtl");
		document.documentElement.setAttribute("lang", "ar");
	} else {
		document.documentElement.setAttribute("dir", "ltr");
		document.documentElement.setAttribute("lang", lang);
	}
}

onMounted(() => {
	const savedLang = localStorage.getItem("locale") || locale.value;
	setLang(savedLang);
});

const dayNames = computed(() => {
	if (locale.value === "fr") {
		return ["Dimanche", "Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi"];
	}
	if (locale.value === "ar") {
		return ["الأحد", "الإثنين", "الثلاثاء", "الأربعاء", "الخميس", "الجمعة", "السبت"];
	}
	return ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
});

const highlights = computed(() => ([
	{
		title: t("ourCareTeam.highlights.approved.title"),
		description: t("ourCareTeam.highlights.approved.description"),
		icon: "shield",
	},
	{
		title: t("ourCareTeam.highlights.evidenceBased.title"),
		description: t("ourCareTeam.highlights.evidenceBased.description"),
		icon: "spark",
	},
	{
		title: t("ourCareTeam.highlights.multilingual.title"),
		description: t("ourCareTeam.highlights.multilingual.description"),
		icon: "globe",
	},
]));

const aboutLinks = computed(() => ([
	{ label: t("ourCareTeam.sidebar.about.items.telementalHealth"), href: route("telemental-health"), active: false },
	{ label: t("ourCareTeam.sidebar.about.items.whoWeAre"), href: route("who-we-are"), active: false },
	{ label: t("ourCareTeam.sidebar.about.items.ourCareTeam"), href: route("our-care-team"), active: true },
	{ label: t("ourCareTeam.sidebar.about.items.termsAndConditions"), href: route("terms-conditions"), active: false },
	{ label: t("ourCareTeam.sidebar.about.items.privacyProtection"), href: route("privacy-protection"), active: false },
]));

const navigationLinks = computed(() => ([
	{ label: t("ourCareTeam.sidebar.navigation.items.about"), href: route("who-we-are"), active: false },
	{ label: t("ourCareTeam.sidebar.navigation.items.ourServices"), href: route("services.index"), active: false },
	{ label: t("ourCareTeam.sidebar.navigation.items.support"), href: route("faq"), active: false },
	{ label: t("ourCareTeam.sidebar.navigation.items.resources"), href: route("ressources.index"), active: false },
	{ label: t("ourCareTeam.sidebar.navigation.items.blog"), href: route("blogs.index"), active: false },
]));

function fullName(profile) {
	const first = (profile?.first_name || "").trim();
	const last = (profile?.last_name || "").trim();
	const combined = `${first} ${last}`.trim();
	return combined || profile?.user?.name || t("ourCareTeam.team.unknownName");
}

function avatarUrl(profile) {
	return resolveStorageUrl(profile?.profile_image_url) || null;
}

function initials(profile) {
	const parts = fullName(profile).split(/\s+/).filter(Boolean);
	const letters = parts.slice(0, 2).map((part) => (part[0] || "").toUpperCase()).join("");
	return letters || "AE";
}

function formatTime(timeStr) {
	if (!timeStr || typeof timeStr !== "string") return "";
	return timeStr.slice(0, 5);
}

function formatPrice(value) {
	if (value === null || value === undefined || value === "") return t("ourCareTeam.team.priceUnknown");
	const num = Number(value);
	if (Number.isNaN(num)) return String(value);
	return num.toFixed(2);
}

function availabilityLines(profile) {
	const items = Array.isArray(profile?.availabilities) ? profile.availabilities : [];
	if (!items.length) return [];

	return items.slice(0, 3).map((slot) => {
		const day = dayNames.value[slot.day_of_week] ?? "";
		return `${day} · ${formatTime(slot.start_time)}-${formatTime(slot.end_time)}`;
	});
}

function availabilityDays(profile) {
	const items = Array.isArray(profile?.availabilities) ? profile.availabilities : [];
	const seen = new Set();
	const days = [];

	for (const slot of items) {
		const index = Number(slot.day_of_week);
		if (Number.isNaN(index)) continue;
		const label = dayNames.value[index] ?? null;
		if (label && !seen.has(label)) {
			seen.add(label);
			days.push(label);
		}
	}

	return days;
}

function languageLabel(lang) {
	const value = String(lang || "").toLowerCase();
	if (locale.value === "fr") {
		if (value === "english") return "Anglais";
		if (value === "french") return "Français";
		if (value === "arabic") return "Arabe";
	}
	if (locale.value === "ar") {
		if (value === "english") return "الإنجليزية";
		if (value === "french") return "الفرنسية";
		if (value === "arabic") return "العربية";
	}
	if (value === "english") return "English";
	if (value === "french") return "French";
	if (value === "arabic") return "Arabic";
	return String(lang || "").trim();
}

function languagesFor(profile) {
	const langs = Array.isArray(profile?.languages) ? profile.languages : [];
	return langs.map(languageLabel).filter(Boolean);
}

function specialisationsFor(profile) {
	const items = Array.isArray(profile?.specialisations) ? profile.specialisations : [];
	return items.map((item) => (item?.name || "").trim()).filter(Boolean);
}

function expertisesFor(profile) {
	const items = Array.isArray(profile?.expertises) ? profile.expertises : [];
	return items.map((item) => (item?.name || "").trim()).filter(Boolean);
}

function ratingStats(profile) {
	const avg = profile?.rating_average;
	const count = profile?.ratings_count;
	return {
		avg: avg != null ? Number(avg) || 0 : null,
		count: Number(count) || 0,
	};
}

function formatRating(profile) {
	const value = ratingStats(profile).avg;
	return value != null ? value.toFixed(1) : "";
}

function ratingCount(profile) {
	return ratingStats(profile).count || 0;
}

function starType(profile, index) {
	const avg = Number(ratingStats(profile).avg || 0);
	if (avg >= index) return "full";
	if (avg >= index - 0.5) return "half";
	return "empty";
}

function selectHref(profile) {
	const bookUrl = route("appointments.book", profile?.id);
	const role = String(props.authUser?.role || "").toUpperCase();

	if (props.authUser && role === "PATIENT") {
		return bookUrl;
	}
	if (props.authUser) {
		return route("dashboard");
	}

	const target = props.canLogin ? route("login") : route("register");
	const params = new URLSearchParams();
	params.set("redirect", bookUrl);
	return `${target}?${params.toString()}`;
}

function accentClass(index) {
	const accents = [
		"from-[#5997ac]/20 via-white to-[#e8b4b8]/25",
		"from-[#af5166]/15 via-white to-[#5997ac]/20",
		"from-[#f4efe6] via-white to-[#5997ac]/15",
	];
	return accents[index % accents.length];
}

function iconPath(icon) {
	if (icon === "shield") {
		return "M12 3l7 3v5c0 4.97-3.05 9.47-7 11-3.95-1.53-7-6.03-7-11V6l7-3z";
	}
	if (icon === "spark") {
		return "M12 3l1.9 5.1L19 10l-5.1 1.9L12 17l-1.9-5.1L5 10l5.1-1.9L12 3zm7 12l.95 2.55L22.5 18l-2.55.45L19 21l-.95-2.55L15.5 18l2.55-.45L19 15zm-14 0l.95 2.55L8.5 18l-2.55.45L5 21l-.95-2.55L1.5 18l2.55-.45L5 15z";
	}
	return "M12 2a10 10 0 100 20 10 10 0 000-20zm6.93 9h-3.17a15.8 15.8 0 00-1.38-5.01A8.03 8.03 0 0118.93 11zM12 4c.88 1.07 1.8 3.02 2.2 5H9.8C10.2 7.02 11.12 5.07 12 4zM4.07 13h3.17c.2 1.83.68 3.55 1.38 5.01A8.03 8.03 0 014.07 13zm3.17-2H4.07a8.03 8.03 0 014.48-5.01A15.8 15.8 0 007.24 11zM12 20c-.88-1.07-1.8-3.02-2.2-5h4.4c-.4 1.98-1.32 3.93-2.2 5zm2.62-1.99A15.8 15.8 0 0015.99 13h3.17a8.03 8.03 0 01-4.54 5.01z";
}
</script>

<template>
	<Head :title="`${t('ourCareTeam.banner.title')} - AEnhance`" />

	<Navbar :canLogin="canLogin" :canRegister="canRegister" :authUser="authUser" />

	<div class="relative w-full h-[180px] overflow-hidden">
		<div class="absolute inset-0 bg-cover bg-center" style="background-image: url('/storage/banners/banner1.jpg')">
			<div class="absolute inset-0 bg-gradient-to-r from-[#5997ac]/80 to-[#e8b4b8]/80"></div>
		</div>

		<div class="relative h-full flex flex-col justify-center container mx-auto px-4 sm:px-6 lg:px-8">
			<h1 class="text-3xl md:text-4xl font-bold text-white mb-3">
				{{ t('ourCareTeam.banner.title') }}
			</h1>
			<div class="flex items-center gap-2 text-white text-sm">
				<Link :href="route('home')" class="hover:underline">{{ t('ourCareTeam.banner.home') }}</Link>
				<span>»</span>
				<span>{{ t('ourCareTeam.banner.about') }}</span>
				<span>»</span>
				<span>{{ t('ourCareTeam.banner.current') }}</span>
			</div>
		</div>
	</div>

	<div class="bg-gray-50 py-12 md:py-16">
		<div class="container mx-auto px-4 sm:px-6 lg:px-8">
			<div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
				<div class="lg:col-span-3 space-y-8">
					<section class="relative overflow-hidden rounded-[28px] bg-white p-6 md:p-8 shadow-sm ring-1 ring-black/5">
						<div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(89,151,172,0.18),_transparent_35%),radial-gradient(circle_at_bottom_right,_rgba(175,81,102,0.16),_transparent_38%)]"></div>
						<div class="relative grid gap-8 xl:grid-cols-[minmax(0,1.3fr)_minmax(280px,0.7fr)] xl:items-start">
							<div>
								<p class="inline-flex items-center rounded-full bg-[#5997ac]/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-[#5997ac]">
									{{ t('ourCareTeam.intro.eyebrow') }}
								</p>
								<h2 class="mt-4 max-w-3xl text-3xl md:text-[2.5rem] font-bold leading-tight text-slate-900">
									{{ t('ourCareTeam.intro.title') }}
								</h2>
								<p class="mt-4 max-w-3xl text-base leading-8 text-slate-600">
									{{ t('ourCareTeam.intro.description') }}
								</p>
							</div>
						</div>
					</section>

					<section class="grid grid-cols-1 md:grid-cols-3 gap-4">
						<article
							v-for="item in highlights"
							:key="item.title"
							class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-black/5 transition duration-300 hover:-translate-y-1 hover:shadow-lg"
						>
							<div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-[#5997ac] to-[#af5166] text-white shadow-lg shadow-[#5997ac]/20">
								<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
									<path :d="iconPath(item.icon)" />
								</svg>
							</div>
							<h3 class="mt-4 text-lg font-semibold text-slate-900">{{ item.title }}</h3>
							<p class="mt-2 text-sm leading-7 text-slate-600">{{ item.description }}</p>
						</article>
					</section>

					<section class="rounded-[28px] bg-white p-6 md:p-8 shadow-sm ring-1 ring-black/5">
						<div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
							<div>
								<h2 class="text-2xl md:text-3xl font-bold text-[#5997ac]">{{ t('ourCareTeam.team.title') }}</h2>
								<p class="mt-2 max-w-3xl text-sm md:text-base leading-7 text-slate-600">{{ t('ourCareTeam.team.subtitle') }}</p>
							</div>
							<Link
								:href="route('services.consultation')"
								class="inline-flex items-center justify-center rounded-full border border-[#5997ac]/20 px-5 py-2.5 text-sm font-semibold text-[#5997ac] transition hover:border-[#5997ac] hover:bg-[#5997ac] hover:text-white"
							>
								{{ t('ourCareTeam.team.viewAllServices') }}
							</Link>
						</div>

						<div v-if="!profiles.length" class="mt-8 rounded-3xl border border-dashed border-slate-200 bg-slate-50 px-6 py-12 text-center text-slate-600">
							{{ t('ourCareTeam.team.empty') }}
						</div>

						<div v-else class="mt-8 grid grid-cols-1 xl:grid-cols-2 gap-6">
							<article
								v-for="(profile, index) in profiles"
								:key="profile.id"
								class="team-card group overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm transition duration-300 hover:-translate-y-1.5 hover:shadow-xl"
							>
								<div :class="['relative overflow-hidden p-5 bg-gradient-to-br', accentClass(index)]">
									<div class="absolute inset-0 opacity-60 bg-[radial-gradient(circle_at_top_right,_rgba(255,255,255,0.9),_transparent_30%)]"></div>
									<div class="relative flex items-start justify-between gap-4">
										<div class="flex min-w-0 items-center gap-4">
											<div class="h-20 w-20 shrink-0 overflow-hidden rounded-[24px] bg-white/80 shadow-md ring-1 ring-white/80">
												<img v-if="avatarUrl(profile)" :src="avatarUrl(profile)" :alt="fullName(profile)" class="h-full w-full object-cover" />
												<div v-else class="flex h-full w-full items-center justify-center bg-gradient-to-br from-[#5997ac]/15 to-[#af5166]/20 text-2xl font-bold text-slate-700">
													{{ initials(profile) }}
												</div>
											</div>
											<div class="min-w-0">
												<h3 class="mt-3 truncate text-xl font-semibold text-slate-900">{{ fullName(profile) }}</h3>
												<p class="mt-1 text-sm text-slate-600">{{ specialisationsFor(profile).slice(0, 2).join(' • ') || expertisesFor(profile).slice(0, 2).join(' • ') }}</p>
											</div>
										</div>

										<div v-if="ratingCount(profile) > 0" class="shrink-0 rounded-2xl bg-white/90 px-3 py-2 text-right shadow-sm">
											<div class="flex items-center justify-end gap-1 text-amber-400">
												<svg v-for="i in 5" :key="`star-${profile.id}-${i}`" xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" aria-hidden="true">
													<defs v-if="starType(profile, i) === 'half'">
														<linearGradient :id="`team-grad-${profile.id}-${i}`" x1="0" x2="1">
															<stop offset="0%" stop-color="#F59E0B" />
															<stop offset="50%" stop-color="#F59E0B" />
															<stop offset="50%" stop-color="#E5E7EB" />
															<stop offset="100%" stop-color="#E5E7EB" />
														</linearGradient>
													</defs>
													<path v-if="starType(profile, i) === 'full'" fill="currentColor" d="M12 .587l3.668 7.431L23.4 9.753l-5.7 5.557L18.836 24 12 20.201 5.164 24l1.135-8.69L.6 9.753l7.732-1.735L12 .587z" />
													<path v-else-if="starType(profile, i) === 'half'" :fill="`url(#team-grad-${profile.id}-${i})`" d="M12 .587l3.668 7.431L23.4 9.753l-5.7 5.557L18.836 24 12 20.201 5.164 24l1.135-8.69L.6 9.753l7.732-1.735L12 .587z" />
													<path v-else fill="none" stroke="currentColor" stroke-width="1.2" d="M12 .587l3.668 7.431L23.4 9.753l-5.7 5.557L18.836 24 12 20.201 5.164 24l1.135-8.69L.6 9.753l7.732-1.735L12 .587z" />
												</svg>
											</div>
											<div class="mt-1 text-sm font-semibold text-slate-900">{{ formatRating(profile) }}</div>
											<div class="text-[11px] text-slate-500">{{ ratingCount(profile) }} {{ t('ourCareTeam.team.ratingsLabel') }}</div>
										</div>
									</div>
								</div>

								<div class="p-5">
									<div v-if="specialisationsFor(profile).length" class="flex flex-wrap gap-2">
										<span
											v-for="label in specialisationsFor(profile)"
											:key="`spec-${profile.id}-${label}`"
											class="rounded-full bg-[#af5166]/10 px-3 py-1 text-xs font-semibold text-[#af5166]"
										>
											{{ label }}
										</span>
									</div>

									<div v-if="expertisesFor(profile).length" class="mt-3 flex flex-wrap gap-2">
										<span
											v-for="label in expertisesFor(profile).slice(0, 4)"
											:key="`exp-${profile.id}-${label}`"
											class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700 ring-1 ring-slate-200"
										>
											{{ label }}
										</span>
									</div>

									<div v-if="languagesFor(profile).length" class="mt-4 flex flex-wrap gap-2">
										<span
											v-for="language in languagesFor(profile)"
											:key="`lang-${profile.id}-${language}`"
											class="rounded-full bg-[#5997ac]/10 px-3 py-1 text-xs font-semibold text-[#5997ac]"
										>
											{{ language }}
										</span>
									</div>

									<p class="mt-4 text-sm leading-7 text-slate-600 line-clamp-4">
										{{ profile.bio || t('ourCareTeam.team.noBio') }}
									</p>

									<div v-if="availabilityLines(profile).length" class="mt-5 rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200/70">
										<div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">{{ t('ourCareTeam.team.availabilityTitle') }}</div>
										<ul class="mt-3 space-y-2 text-sm text-slate-700">
											<li v-for="line in availabilityLines(profile)" :key="`${profile.id}-${line}`" class="flex items-center gap-2">
												<span class="h-2 w-2 rounded-full bg-[#5997ac]"></span>
												<span>{{ line }}</span>
											</li>
										</ul>
									</div>

									<div v-else-if="availabilityDays(profile).length" class="mt-5 flex flex-wrap gap-2">
										<span
											v-for="day in availabilityDays(profile)"
											:key="`day-${profile.id}-${day}`"
											class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700 ring-1 ring-slate-200"
										>
											{{ day }}
										</span>
									</div>

									<div class="mt-6 flex flex-col gap-3 sm:flex-row">
										<Link
											:href="selectHref(profile)"
											class="inline-flex flex-1 items-center justify-center rounded-full bg-[#5997ac] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#4d8699] hover:shadow-lg"
										>
											{{ t('ourCareTeam.team.book') }}
										</Link>
									</div>
								</div>
							</article>
						</div>
					</section>
				</div>

				<div class="lg:col-span-1">
					<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
						<h3 class="text-xl font-bold text-[#5997ac] mb-4 pb-3 border-b-2 border-[#5997ac]">
							{{ t('ourCareTeam.sidebar.about.title') }}
						</h3>
						<ul class="space-y-2">
							<li v-for="item in aboutLinks" :key="item.label">
								<Link
									:href="item.href"
									:class="[
										'flex items-center gap-2 py-2 transition-colors',
										item.active ? 'text-[#af5166] font-semibold' : 'text-gray-700 hover:text-[#af5166]'
									]"
								>
									<span class="text-[#af5166]">›</span>
									{{ item.label }}
								</Link>
							</li>
						</ul>
					</div>

					<div class="bg-white rounded-lg shadow-sm p-6">
						<h3 class="text-xl font-bold text-[#5997ac] mb-4 pb-3 border-b-2 border-[#5997ac]">
							{{ t('ourCareTeam.sidebar.navigation.title') }}
						</h3>
						<ul class="space-y-2">
							<li v-for="item in navigationLinks" :key="item.label">
								<Link
									:href="item.href"
									class="flex items-center gap-2 text-gray-700 hover:text-[#af5166] transition-colors py-2"
								>
									<span class="text-[#af5166]">›</span>
									{{ item.label }}
								</Link>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>

	<Footer />
</template>

<style scoped>
[dir="rtl"] {
	text-align: right;
}

.team-card {
	position: relative;
}

.team-card::before {
	content: "";
	position: absolute;
	inset: 0;
	background: linear-gradient(120deg, transparent 20%, rgba(255, 255, 255, 0.55) 40%, transparent 60%);
	opacity: 0;
	transform: translateX(-120%);
	transition: opacity 0.35s ease;
	pointer-events: none;
}

.team-card:hover::before {
	opacity: 1;
	animation: team-shine 1s ease;
}

@keyframes team-shine {
	from {
		transform: translateX(-120%);
	}
	to {
		transform: translateX(120%);
	}
}
</style>
