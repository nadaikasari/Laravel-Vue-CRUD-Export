<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { z } from 'zod';
import axios from 'axios';

const props = defineProps<{
  dataOrder?: {
    id: number;
    customer_name: string;
    order_date: string;
  };
  dataProductOrder?: {
    id: number;
    product_name: string;
    quantity: number;
    price: number;
  }[];
}>();

// Zod validation schema
const formSchema = z.object({
  customer_name: z.string().nonempty('Customer name is required'),
  order_date: z.string().nonempty('Order date is required'),
  products: z.array(z.object({
    product_name: z.string().nonempty('Product name is required'),
    quantity: z.number().min(1, 'Quantity must be at least 1'),
    price: z.number().min(0.01, 'Price must be greater than 0'),
  })).nonempty('At least one product is required'),
});

const formData = ref({
  order_id: null as number | null,
  customer_name: '',
  order_date: '',
  products: [] as { product_name: string, quantity: number, price: number }[],
});

const formErrors = ref({
  customer_name: '',
  order_date: '',
  products: [] as string[],
});

const isProcessing = ref(false);

const addProduct = () => {
  formData.value.products.push({
    product_name: '',
    quantity: 1,
    price: 1,
  });
};

const removeProduct = (index: number) => {
  formData.value.products.splice(index, 1);
};

const validateForm = () => {
  try {
    formSchema.parse(formData.value);
    return true;
  } catch (e) {
    if (e instanceof z.ZodError) {
      // Map errors from Zod to our formErrors object
      const errors: any = {};
      e.errors.forEach(error => {
        const path = error.path[0];
        if (path === 'products') {
          const index = error.path[1];
          errors.products = errors.products || [];
          errors.products[index] = error.message;
        } else {
          errors[path] = error.message;
        }
      });
      formErrors.value = errors;
    }
    return false;
  }
};

const submitForm = async () => {
  if (!validateForm()) return;

  isProcessing.value = true;

  try {
    // Send the updated data to the server
    const response = await axios.put(`/orders/${props.dataOrder.id}`, formData.value, {
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
    });

    alert(response.data.message); 

    router.get(`/orders`, {}, {
      preserveState: true,
      preserveScroll: true,
      replace: true
    });

  } catch (error) {
    if (error.response && error.response.data.message) {
      alert(error.response.data.message);
    } else {
      alert('Failed to update order');
    }
  } finally {
    isProcessing.value = false;
  }
};

const navigateToOrderIndex = () => {
  router.get(`/orders`, {}, {
    preserveState: true,
    preserveScroll: true,
    replace: true
  });
};

onMounted(() => {
  if (props.dataOrder) {
    formData.value.order_id = props.dataOrder.id;
    formData.value.customer_name = props.dataOrder.customer_name;
    formData.value.order_date = props.dataOrder.order_date;
  }

  if (props.dataProductOrder) {
    formData.value.products = props.dataProductOrder.map(product => ({
      id: product.id ?? 0,
      product_name: product.product_name,
      quantity: Number(product.quantity),
      price: Number(product.price),
    }));
  }
});
</script>

<template>
  <div class="container py-4">
    <Head title="Edit Order" />
    <h1 class="text-3xl font-bold mb-4">Edit Order</h1>

    <!-- Form -->
    <form @submit.prevent="submitForm">
      <div class="mb-3 row">
        <!-- Customer Name -->
        <div class="col-md-6">
          <label for="customer_name" class="form-label">Customer Name</label>
          <input
            id="customer_name"
            v-model="formData.customer_name"
            type="text"
            class="form-control"
            placeholder="Enter name"
          />
          <div v-if="formErrors.customer_name" class="text-danger">
            {{ formErrors.customer_name }}
          </div>
        </div>

        <!-- Order Date -->
        <div class="col-md-6">
          <label for="order_date" class="form-label">Order Date</label>
          <input
            id="order_date"
            v-model="formData.order_date"
            type="date"
            class="form-control"
          />
          <div v-if="formErrors.order_date" class="text-danger">
            {{ formErrors.order_date }}
          </div>
        </div>
      </div>

      <!-- Product List -->
      <div class="mb-4">
        <h3 class="h5">Products List</h3>
        <div v-for="(product, index) in formData.products" :key="index" class="mb-3 d-flex gap-3 align-items-end">
          <div class="flex-grow-1">
            <label for="product_name" class="form-label">Product Name</label>
            <input
              v-model="product.product_name"
              type="text"
              class="form-control"
              placeholder="Product Name"
            />
            <div v-if="formErrors.products?.[index]" class="text-danger">
              {{ formErrors.products[index] }}
            </div>
          </div>

          <div class="flex-grow-1">
            <label for="quantity" class="form-label">Quantity</label>
            <input
              v-model="product.quantity"
              type="number"
              min="1"
              class="form-control"
              placeholder="Quantity"
            />
          </div>

          <div class="flex-grow-1">
            <label for="price" class="form-label">Price</label>
            <input
              v-model="product.price"
              type="number"
              min="0.01"
              step="0.01"
              class="form-control"
              placeholder="Price"
            />
          </div>

          <button
            type="button"
            class="btn btn-danger btn-sm"
            @click="removeProduct(index)">
            Remove
          </button>
        </div>

        <!-- Add Product Button -->
        <button type="button" class="btn btn-primary" @click="addProduct">Add Product</button>
      </div>

      <div class="d-flex justify-content-end">
        <!-- Cancel Button -->
        <button
          type="button"
          class="btn btn-danger btn-sm mr-2"
          @click="navigateToOrderIndex()">
          Cancel
        </button>

        <!-- Submit Button -->
        <button
          type="submit"
          class="btn btn-success"
          :disabled="isProcessing"
          :class="{ 'disabled': isProcessing }">
          Update
        </button>
      </div>
    </form>
  </div>
</template>
