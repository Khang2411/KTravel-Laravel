<script setup>
import Chart from 'chart.js/auto';
import { ref, onMounted } from 'vue'

const canvasBarRef = ref(null)
const canvasDoughnutRef = ref(null)
const canvasLineRef = ref(null)

const props = defineProps({
    product_number: Number,
    order_number: Number,
    user_number: Number,
    revenueByMonths: Array,
    orderByMonths: Array,
})

onMounted(() => {
    const labels = ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6', 'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'];
    new Chart(canvasLineRef.value, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Thống kê doanh thu theo tháng',
                data: props.revenueByMonths,
                fill: false,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: "rgb(75, 192, 192)",
                tension: 0.1
            }]
        },
        options: {
            plugins: {
                legend: {
                    display: true,
                    labels: {
                        color: '#42a3a6'
                    }
                }
            },
            responsive: true,
            maintainAspectRatio: false
        }
    });
})

onMounted(() => {
    new Chart(canvasBarRef.value, {
        type: 'bar',
        data: {
            labels: ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6', 'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'],
            datasets: [{
                label: 'Thống kê số đơn đặt hàng theo tháng',
                data: props.orderByMonths,
                backgroundColor: [
                    '#ffd333'
                ],
                borderWidth: 1
            }]
        },
        options: {
            plugins: {
                legend: {
                    display: true,
                    labels: {
                        color: '#42a3a6'
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            responsive: true,
            maintainAspectRatio: false
        }
    });
})

onMounted(() => {
    new Chart(canvasDoughnutRef.value, {
        type: 'doughnut',
        data: {
            labels: ['Sản phẩm', 'Đơn đặt hàng', 'Khách hàng'],
            datasets: [{
                label: 'Tổng: ',
                data: [props.product_number, props.order_number, props.user_number],
                backgroundColor: [
                    'rgb(255, 99, 132)',
                    'rgb(54, 162, 235)',
                    'rgb(255, 205, 86)'
                ],
                hoverOffset: 4
            }]
        }, options: {
            plugins: {
                legend: {
                    display: true,
                    labels: {
                        color: '#42a3a6'
                    }
                }
            },
            responsive: true,
            maintainAspectRatio: false
        }
    });
})
</script>

<template>
    <div>
        <div class="grid grid-cols-3 gap-4">
            <div class="col-span-3 bg-gray-50 dark:bg-gray-800 p-2 h-80">
                <canvas ref="canvasLineRef"></canvas>
            </div>
            <div class="col-span-3 md:col-span-2 bg-gray-50 dark:bg-gray-800 p-2">
                <canvas ref="canvasBarRef"></canvas>
            </div>
            <div class="col-span-3 md:col-span-1 bg-gray-50 dark:bg-gray-800">
                <canvas ref="canvasDoughnutRef"></canvas>
            </div>
        </div>

    </div>
</template>

<style lang="scss" scoped></style>