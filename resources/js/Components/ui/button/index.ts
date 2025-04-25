import { type VariantProps, cva } from 'class-variance-authority';

export { default as Button } from './Button.vue';

export const buttonVariants = cva(
  'inline-flex items-center justify-center whitespace-nowrap rounded-md min-h-[52px] text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:bg-gray-300 cursor-pointer',
  {
    variants: {
      variant: {
        default: 'bg-prijma-primary text-primary-foreground hover:bg-prijma-primary/80 font-bold',
        destructive:
          'bg-destructive text-destructive-foreground hover:bg-destructive/90',
        outline:
          'border border-prijma-stroke bg-background hover:bg-accent hover:text-accent-foreground text-prijma-text-primary font-normal',
        secondary:
          'bg-secondary text-secondary-foreground hover:bg-secondary/80',
        ghost: 'hover:bg-accent hover:text-accent-foreground',
        link: 'text-primary underline-offset-4 hover:underline',
        'outline-primary': 'border-[1.5px] border-prijma-primary text-prijma-primary bg-background hover:bg-prijma-primary-lighter cursor-pointer',
        'outline-destructive': 'border-[1.5px] border-prijma-danger text-prijma-danger bg-background hover:bg-prijma-danger-lighter cursor-pointer',
      },
      size: {
        default: 'h-10 px-4 py-2',
        xs: 'h-7 rounded px-2',
        sm: 'h-9 rounded-md px-3',
        lg: 'h-11 rounded-md px-8',
        icon: 'h-10 w-10',
      },
    },
    defaultVariants: {
      variant: 'default',
      size: 'default',
    },
  },
);

export type ButtonVariants = VariantProps<typeof buttonVariants>
