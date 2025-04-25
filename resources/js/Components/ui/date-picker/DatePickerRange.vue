<script setup lang="ts">
    import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '../dropdown-menu';
    import { Button } from '../button';
    import { CalendarDays, ChevronLeft, ChevronRight } from 'lucide-vue-next';
    import { computed, ref } from 'vue';
    import dayjs, { Dayjs } from 'dayjs';
    import { cn } from '@/lib/utils';

    const { periode } = defineProps<{
    periode: {
        from: string;
        to: string;
    };
    }>();

    const showDropdown = ref(false);
        const selectedDate = ref({
        from: null as dayjs.Dayjs | null,
        to: null as dayjs.Dayjs | null
    });

    const currentDate = ref(dayjs());  

    const currentYear = computed(() => currentDate.value.year());
    const currentMonth = computed(() => currentDate.value.month());

    // Function to get the days for the current month
    const getDaysInMonth = () => {
        const daysInMonth = currentDate.value.daysInMonth();  // Get the number of days in the current month
        return Array.from({ length: daysInMonth }, (_, i) => ({
            index: i + 1,  // Day number (1-based index)
            name: dayjs(currentDate.value).date(i + 1).format('D'), // Display day number
        }));
    };

    const days = ref(getDaysInMonth());

    const goToPreviousMonth = () => {
        currentDate.value = currentDate.value.subtract(1, 'month');  // Move to previous month
        days.value = getDaysInMonth();  // Update the days for the new month
    };

    const goToNextMonth = () => {
        currentDate.value = currentDate.value.add(1, 'month');  // Move to next month
        days.value = getDaysInMonth();  // Update the days for the new month
    };

    // Handle day selection and range selection (from-to)
    const handleSelectedDay = (day: number) => {
        const selectedDates = dayjs(currentDate.value).date(day);

        // If no start day is selected, set this as the start day
        if (!selectedDate.value.from || selectedDate.value.to) {
            selectedDate.value.from = selectedDates;
            selectedDate.value.to = null;
            activeIndex.value.from = selectedDates;
            activeIndex.value.to = null;
        } else {
            // If a start day is selected and the new date is before it, set this as the start date
            if (selectedDates.isBefore(selectedDate.value.from)) {
            selectedDate.value.to = selectedDate.value.from;
            selectedDate.value.from = selectedDates;
            activeIndex.value.from = selectedDates;
            activeIndex.value.to = selectedDate.value.to;
            } else {
            // If the new date is after the start date, set this as the end date
            selectedDate.value.to = selectedDates;
            activeIndex.value.to = selectedDates;
            }
        }
    };

    const activeIndex = ref({
        from: null as dayjs.Dayjs | null,
        to: null as dayjs.Dayjs | null,
    });

    const isActiveRange = computed(() => (date: Dayjs) => {
        const from = activeIndex.value.from || dayjs();
        const to = activeIndex.value.to || dayjs();
        return date >= from && date <= to;
        });

        const isActiveStartEndDate = computed(() => (index: number) => {
        const from = activeIndex.value.from;
        const to = activeIndex.value.to;

        const isFromActive = from?.date() === index && from?.year() === currentYear.value;

        const isToActive = to?.date() === index && to?.year() === currentYear.value;

        return isFromActive || isToActive;
    });

    const emit = defineEmits<{
        (e: 'periode', value: {
            from: string
            to: string
        })
    }>();

    const handleApply = () => {
        emit('periode', {
            from: selectedDate.value.from?.toString() as string,
            to: selectedDate.value.to?.toString() as string,
        });

        showDropdown.value = false;
    };

    const handleReset = () => {
        resetDate();

        showDropdown.value = false;
    };

    const resetDate = () => {
        selectedDate.value = {
            from: null as dayjs.Dayjs | null,
            to: null as dayjs.Dayjs | null
        };

        activeIndex.value = {
            from: null as dayjs.Dayjs | null,
            to: null as dayjs.Dayjs | null
        };

        emit('periode', {
            from: '',
            to: ''
        });
    };

</script>

<template>
  <DropdownMenu @update:open="showDropdown = $event" :open="showDropdown">
    <DropdownMenuTrigger asChild>
      <Button :variant="periode.from ? 'outline-primary' : 'outline'"
        class="flex justify-between gap-2 min-w-[282px] min-h-[57px]">
        <span v-if="!selectedDate.from">Periode</span>
        <span v-else>
          {{ selectedDate.from?.format('DD MMMM YYYY') }} - {{ selectedDate.to?.format('DD MMMM YYYY') }}
        </span>
        <CalendarDays />
      </Button>
    </DropdownMenuTrigger>
    <DropdownMenuContent class="w-[400px] p-6 mt-2">
      <div class="flex items-center justify-between pb-3 border-b border-prijma-stroke">
        <span class="text-lg font-bold">
          {{ currentDate.format('MMMM YYYY') }}
        </span>
        <div class="">
          <Button variant="ghost" @click="goToPreviousMonth()"
            class="min-h-[30px] min-w-[30px] p-0 text-prijma-primary hover:text-prijma-primary hover:bg-prijma-primary-lighter">
            <ChevronLeft />
          </Button>
          <Button variant="ghost" @click="goToNextMonth()"
            class="min-h-[30px] min-w-[30px] p-0 text-prijma-primary hover:text-prijma-primary hover:bg-prijma-primary-lighter">
            <ChevronRight />
          </Button>
        </div>
      </div>

      <div class="grid grid-cols-6 mt-4">
        <Button v-for="day in days" :key="day.index" variant="ghost" @click="handleSelectedDay(day.index)"
          :class="cn(
            'text-base rounded-none font-normal',
            ((activeIndex.from !== null && activeIndex.to !== null) && isActiveRange(dayjs().year(currentYear).month(currentMonth).date(day.index))) && 'bg-prijma-primary-lighter hover:bg-prijma-primary-lighter/80',
            isActiveStartEndDate(day.index) && 'bg-prijma-primary text-white hover:bg-prijma-primary/80 hover:text-white',
          )">
          {{ day.name }} 
        </Button>
      </div>

      <div class="flex items-center justify-end gap-4 mt-4">
        <Button variant="outline-primary" class="min-w-[114px]" @click="handleReset()">
          Reset
        </Button>
        <Button class="min-w-[114px]" :disabled="!selectedDate.from || !selectedDate.to" @click="handleApply()">
          Apply
        </Button>
      </div>
    </DropdownMenuContent>
  </DropdownMenu>
</template>
