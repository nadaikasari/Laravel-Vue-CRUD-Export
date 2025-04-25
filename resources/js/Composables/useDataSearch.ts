import { Ref, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import debounce from 'lodash.debounce';

export function useDataSearch(baseUrl: string, initialPerPage = '10', additionalFilters: Ref<Record<string, any>> = ref({})) {
    const searchQuery = ref('');
    const itemsPerPage = ref(initialPerPage);
    const currentPage = ref(1);

    const debouncedSearch = debounce(() => {
        performSearch();
    }, 300);

    const performSearch = () => {
        router.get(baseUrl, {
            search: searchQuery.value,
            page: 1,
            per_page: itemsPerPage.value,
            ...additionalFilters.value,
        }, {
            preserveState: true,
            preserveScroll: true,
            replace: true
        });
    };

    const changePage = (newPage: number) => {
        currentPage.value = newPage;
        router.get(baseUrl, {
            page: newPage,
            per_page: itemsPerPage.value,
            search: searchQuery.value,
            ...additionalFilters.value,
        }, {
            preserveState: true,
            preserveScroll: true
        });
    };

    const changeItemsPerPage = (value: string) => {
        itemsPerPage.value = value;
        router.get(baseUrl, {
            page: currentPage.value,
            per_page: value,
            search: searchQuery.value,
            ...additionalFilters.value,
        }, {
            preserveState: true,
            preserveScroll: true,
            replace: true
        });
    };

    watch(searchQuery, () => {
        debouncedSearch();
    });

    watch(itemsPerPage, (newValue) => {
        if (!newValue) {
            itemsPerPage.value = '10';
        }
    });

    watch(additionalFilters, performSearch, { deep: true });

    const initFromUrl = () => {
        const urlParams = new URLSearchParams(window.location.search);
        itemsPerPage.value = urlParams.get('per_page') || initialPerPage;
        currentPage.value = Number(urlParams.get('page')) || 1;
        searchQuery.value = urlParams.get('search') || '';
        for (const key in additionalFilters.value) {
            additionalFilters.value[key] = '';
        }
    };

    return {
        searchQuery,
        itemsPerPage,
        currentPage,
        changePage,
        changeItemsPerPage,
        initFromUrl
    };
}