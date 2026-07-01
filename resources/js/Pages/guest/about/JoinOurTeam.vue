<script setup>
import { Head, Link } from "@inertiajs/vue3";
import { computed, onMounted } from "vue";
import { useI18n } from "vue-i18n";
import Navbar from "@/Components/Navbar.vue";
import Footer from "@/Components/Footer.vue";

const props = defineProps({
	canLogin: { type: Boolean },
	canRegister: { type: Boolean },
	authUser: { type: Object },
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

const aboutLinks = computed(() => ([
	{ label: t("joinOurTeamPage.sidebar.about.items.telementalHealth"), href: route("telemental-health"), active: false },
	{ label: t("joinOurTeamPage.sidebar.about.items.whoWeAre"), href: route("who-we-are"), active: false },
	{ label: t("joinOurTeamPage.sidebar.about.items.ourCareTeam"), href: route("our-care-team"), active: false },
	{ label: t("joinOurTeamPage.sidebar.about.items.joinOurTeam"), href: route("join-our-team"), active: true },
	{ label: t("joinOurTeamPage.sidebar.about.items.termsAndConditions"), href: route("terms-conditions"), active: false },
	{ label: t("joinOurTeamPage.sidebar.about.items.privacyProtection"), href: route("privacy-protection"), active: false },
]));

const navigationLinks = computed(() => ([
	{ label: t("joinOurTeamPage.sidebar.navigation.items.about"), href: route("who-we-are") },
	{ label: t("joinOurTeamPage.sidebar.navigation.items.ourServices"), href: route("services.index") },
	{ label: t("joinOurTeamPage.sidebar.navigation.items.support"), href: route("faq") },
	{ label: t("joinOurTeamPage.sidebar.navigation.items.resources"), href: route("ressources.index") },
	{ label: t("joinOurTeamPage.sidebar.navigation.items.blog"), href: route("blogs.index") },
]));

const infoBlocks = computed(() => ([
	{
		title: t("joinOurTeamPage.benefits.flexibility.title"),
		description: t("joinOurTeamPage.benefits.flexibility.description"),
	},
	{
		title: t("joinOurTeamPage.benefits.reach.title"),
		description: t("joinOurTeamPage.benefits.reach.description"),
	},
	{
		title: t("joinOurTeamPage.benefits.platform.title"),
		description: t("joinOurTeamPage.benefits.platform.description"),
	},
]));

const ctaHref = computed(() => {
	if (props.authUser) return route("dashboard");
	if (props.canRegister) return route("register");
	if (props.canLogin) return route("login");
	return route("home");
});

const ctaLabel = computed(() => {
	if (props.authUser) return t("joinOurTeamPage.cta.dashboard");
	if (props.canRegister) return t("joinOurTeamPage.cta.register");
	if (props.canLogin) return t("joinOurTeamPage.cta.login");
	return t("joinOurTeamPage.cta.learnMore");
});
</script>

<template>
	<Head :title="`${t('joinOurTeamPage.banner.title')} - AEnhance`" />

	<Navbar :canLogin="canLogin" :canRegister="canRegister" :authUser="authUser" />

	<div class="relative w-full h-[180px] overflow-hidden">
		<div class="absolute inset-0 bg-cover bg-center" style="background-image: url('/storage/banners/banner1.jpg')">
			<div class="absolute inset-0 bg-gradient-to-r from-[#5997ac]/80 to-[#e8b4b8]/80"></div>
		</div>

		<div class="relative h-full flex flex-col justify-center container mx-auto px-4 sm:px-6 lg:px-8">
			<h1 class="text-3xl md:text-4xl font-bold text-white mb-3">
				{{ t('joinOurTeamPage.banner.title') }}
			</h1>
			<div class="flex items-center gap-2 text-white text-sm">
				<Link :href="route('home')" class="hover:underline">{{ t('joinOurTeamPage.banner.home') }}</Link>
				<span>»</span>
				<span>{{ t('joinOurTeamPage.banner.about') }}</span>
				<span>»</span>
				<span>{{ t('joinOurTeamPage.banner.current') }}</span>
			</div>
		</div>
	</div>

	<div class="bg-gray-50 py-12 md:py-16">
		<div class="container mx-auto px-4 sm:px-6 lg:px-8">
			<div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
				<div class="lg:col-span-3 space-y-8">
					<section class="relative overflow-hidden rounded-[32px] bg-white shadow-sm ring-1 ring-black/5">
						<div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(89,151,172,0.15),_transparent_34%),radial-gradient(circle_at_bottom_right,_rgba(175,81,102,0.14),_transparent_32%)]"></div>
						<div class="relative grid gap-8 px-6 py-8 md:px-8 md:py-10 xl:grid-cols-[minmax(0,1.1fr)_320px] xl:items-center">
							<div>
								<p class="inline-flex items-center rounded-full bg-[#5997ac]/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-[#5997ac]">
									{{ t('joinOurTeamPage.hero.eyebrow') }}
								</p>
								<h2 class="mt-4 max-w-3xl text-3xl md:text-[2.7rem] font-bold leading-tight text-slate-900">
									{{ t('joinOurTeamPage.hero.title') }}
								</h2>
								<p class="mt-5 max-w-2xl text-base leading-8 text-slate-600">
									{{ t('joinOurTeamPage.hero.description') }}
								</p>

								<div class="mt-7 flex flex-col gap-3 sm:flex-row">
									<Link
										:href="ctaHref"
										class="inline-flex items-center justify-center rounded-full bg-[#af5166] px-6 py-3 text-sm font-semibold text-white transition hover:bg-[#964559] hover:shadow-lg"
									>
										{{ ctaLabel }}
									</Link>
								</div>
							</div>

							<div class="flex justify-center xl:justify-end">
								<img src="/storage/home/aenhance-psychologues-85.png" alt="Join Our Team" class="w-full max-w-[280px] object-contain" />
							</div>
						</div>
					</section>

					<section class="rounded-[32px] bg-white p-6 md:p-8 shadow-sm ring-1 ring-black/5">
						<h2 class="text-2xl md:text-3xl font-bold text-[#5997ac]">{{ t('joinOurTeamPage.steps.title') }}</h2>
						<p class="mt-3 max-w-3xl text-sm md:text-base leading-7 text-slate-600">{{ t('joinOurTeamPage.steps.description') }}</p>

						<div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-5">
							<article
								v-for="item in infoBlocks"
								:key="item.title"
								class="rounded-[24px] bg-slate-50 p-5 ring-1 ring-slate-200/80"
							>
								<h3 class="text-lg font-semibold text-slate-900">{{ item.title }}</h3>
								<p class="mt-3 text-sm leading-7 text-slate-600">{{ item.description }}</p>
							</article>
						</div>

						<div class="mt-8 rounded-[24px] bg-gradient-to-r from-[#5997ac]/10 to-[#af5166]/10 px-5 py-5 ring-1 ring-[#5997ac]/10">
							<h3 class="text-lg font-semibold text-slate-900">{{ t('joinOurTeamPage.requirements.title') }}</h3>
							<ul class="mt-4 space-y-3">
								<li v-for="item in 4" :key="item" class="flex items-start gap-3 text-sm leading-7 text-slate-600">
									<span class="mt-2 h-2.5 w-2.5 shrink-0 rounded-full bg-[#af5166]"></span>
									<span>{{ t(`joinOurTeamPage.requirements.items.${item}`) }}</span>
								</li>
							</ul>
						</div>
					</section>

					<section class="grid grid-cols-1 md:grid-cols-2 gap-5">
						<article
							class="rounded-[28px] bg-white p-6 shadow-sm ring-1 ring-black/5"
						>
							<h3 class="text-xl font-semibold text-slate-900">{{ t('joinOurTeamPage.steps.items.create') }}</h3>
							<p class="mt-3 text-sm leading-7 text-slate-600">{{ t('joinOurTeamPage.steps.items.complete') }}</p>
						</article>
						<article class="rounded-[28px] bg-white p-6 shadow-sm ring-1 ring-black/5">
							<h3 class="text-xl font-semibold text-slate-900">{{ t('joinOurTeamPage.steps.items.launch') }}</h3>
							<p class="mt-3 text-sm leading-7 text-slate-600">{{ t('joinOurTeamPage.finalCta.description') }}</p>
							<Link
								:href="ctaHref"
								class="mt-5 inline-flex items-center justify-center rounded-full bg-[#af5166] px-6 py-3 text-sm font-semibold text-white transition hover:bg-[#964559]"
							>
								{{ ctaLabel }}
							</Link>
						</article>
					</section>
				</div>

				<div class="lg:col-span-1">
					<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
						<h3 class="text-xl font-bold text-[#5997ac] mb-4 pb-3 border-b-2 border-[#5997ac]">
							{{ t('joinOurTeamPage.sidebar.about.title') }}
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
</style>
