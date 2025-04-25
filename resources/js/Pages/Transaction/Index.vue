<script setup lang="ts">
    import { Head, router } from '@inertiajs/vue3';
    import { ref, onMounted } from 'vue';
    import { useFormattedDate } from '@/Composables/useFormattedDate';
    import { useFormattedCurrency } from '@/Composables/useFormattedCurrency';
    import { useDataSearch } from '@/Composables/useDataSearch';
    import { Input } from '@/Components/ui/input';
    import { Download, Trash, Pencil } from 'lucide-vue-next';
    import { Button } from '@/Components/ui/button';
    import DatePickerRange from '@/Components/ui/date-picker/DatePickerRange.vue';
    import * as bootstrap from 'bootstrap';
    import axios from 'axios';

    defineProps({
        orders: Object
    });

    type Filters = {
      periode: {
          from: string
          to: string
      }
    }
    const filters = ref<Filters>({
      periode: {
          from: '',
          to: ''
      },
    });
    
    const { formatDateWithoutTime } = useFormattedDate();
    const { formatCurrency } = useFormattedCurrency();
    const { searchQuery, initFromUrl } = useDataSearch(
        `/orders`,
        '10',
        filters
    );

    const selectedOrderId = ref<number | null>(null);

    const openDeleteModal = (id: number) => {
      selectedOrderId.value = id;
      const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
      modal.show();
    };

    const confirmDelete = () => {
      if (selectedOrderId.value !== null) {
        axios
          .delete(`/orders/${selectedOrderId.value}`)
          .then((response) => {
            const modal = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
            modal.hide();

            window.location.reload();
            
            alert(response.data.message); 

          })
          .catch((error) => {
            console.error('Error deleting order:', error);
          });
      }
    };

    const navigateToCreateOrder = () => {
      router.get(`/orders/create`, {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true
      });
    };

    const navigateToUpdateOrder = (orderId: number) => {
      router.get(`/orders/${orderId}/edit`, {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true
      });
    };

  const exportData = async () => {
    try {
      const response = await axios.get('/orders/export');
      const fileName = response.data.file;

      const link = document.createElement('a');
      link.href = `/orders/download/${fileName}`;
      link.download = fileName;  
      link.click(); 
    } catch (error) {
      console.error('Error exporting data:', error);
    }
  };

  onMounted(() => {
    initFromUrl();
  });

</script>

  <template>
    <Head title="Orders" />

    <div class="container py-4">
      <div class="flex items-center justify-between mb-4">
          <h1 class="text-3xl font-bold">Daftar Order</h1>
          <div>
            <Button class="min-w-[154px] bg-blue-600 text-white hover:bg-blue-700 mr-2" @click="navigateToCreateOrder">
                Tambah Order
            </Button>
            <Button class="min-w-[154px] bg-green-600 text-white hover:bg-green-700" @click="exportData">
                Export Data <Download class="ml-2"/>
            </Button>
          </div>
      </div>

      <div class="flex items-center justify-end mb-4">
        <div class="relative mb-4 mr-2">
          <DatePickerRange :periode="filters.periode" @periode="filters.periode = $event" />
        </div>
          <div class="relative mb-4">
              <Input v-model="searchQuery" placeholder="Search..." class="w-full max-w-sm pl-8 min-w-[252px]" />
          </div>
      </div>

    <table class="table table-bordered table-striped">
      <thead class="table-light">
        <tr>
          <th>No</th>
          <th>Order No</th>
          <th>Customer</th>
          <th>Tanggal</th>
          <th>Total</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="orders.data.length === 0">
            <td colspan="6" class="text-center text-muted">Tidak ada data order.</td>
        </tr>
        <tr v-for="(order, index) in orders.data" :key="order.id">
            <td>{{ index + 1 + ((orders.current_page - 1) * orders.per_page) }}</td>
            <td>{{ order.order_no }}</td>
            <td>{{ order.customer_name }}</td>
            <td>{{ formatDateWithoutTime(order.order_date) }}</td>
            <td>{{ formatCurrency(order.grand_total) }}</td>
            <td>
              <Button class="bg-blue-600 text-white hover:bg-blue-700 mr-2" @click="navigateToUpdateOrder(order.id)">
                <Pencil/>
              </Button>
              <Button class="bg-red-600 text-white hover:bg-red-700" @click="openDeleteModal(order.id)">
                <Trash/>
              </Button>
            </td>
        </tr>
      </tbody>
    </table>

    <div class="d-flex justify-content-between align-items-center">
      <!-- Previous Button -->
      <button
        v-if="orders.prev_page_url"
        @click="$inertia.visit(orders.prev_page_url)"
        class="btn btn-outline-secondary"
      >
        Previous
      </button>

      <!-- Next Button -->
      <button
        v-if="orders.next_page_url"
        @click="$inertia.visit(orders.next_page_url)"
        class="btn btn-outline-secondary"
      >
        Next
      </button>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Are you sure you want to delete this order?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-danger" @click="confirmDelete">Delete</button>
        </div>
      </div>
    </div>
  </div>

  <Toaster />

</template>
