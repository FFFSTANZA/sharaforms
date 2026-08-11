<template>
  <input-wrapper v-bind="inputWrapperProps">
    <template #label>
      <slot name="label" />
    </template>

    <div class="stars-outer flex flex-col items-start"
      role="slider"
      :aria-valuemin="0"
      :aria-valuemax="starsCount"
      :aria-valuenow="compVal"
      :aria-label="`Rating: ${compVal} out of ${starsCount} stars`"
    >
      <div class="stars-row flex items-center gap-2">
        <div
          v-for="i in starsCount"
          :key="i"
          :class="ui.star({
            disabled: disabled,
            isActive: i <= compVal,
            isHover: i > compVal && i <= hoverRating,
            class: props.ui?.slots?.star
          })"
          role="button"
          :tabindex="getStarTabIndex(i)"
          :aria-label="`${i} star${i > 1 ? 's' : ''}`"
          @click="setRating(i)"
          @mouseenter="onMouseHover(i)"
          @mouseleave="hoverRating = -1"
          @keydown="handleKeydown($event, i)"
        >
          <Icon
            name="lucide:star"
            :class="ui.icon({ class: props.ui?.slots?.icon })"
          />
        </div>
      </div>

      <Transition name="star-value">
        <div
          v-if="hoverRating > 0 || compVal > 0"
          class="star-value mt-2 flex items-baseline gap-1.5 text-sm text-neutral-400 dark:text-neutral-500"
          aria-hidden="true"
        >
          <span class="font-semibold text-neutral-700 dark:text-neutral-200 tabular-nums">{{ hoverRating > 0 ? hoverRating : compVal }}</span>
          <span>of {{ starsCount }}</span>
        </div>
      </Transition>
    </div>

    <template #help>
      <slot name="help" />
    </template>
    <template #error>
      <slot name="error" />
    </template>
  </input-wrapper>
</template>

<script>
import { inputProps, useFormInput } from "../useFormInput.js"
import { ratingInputTheme } from "~/lib/forms/themes/rating-input.theme.js"

export default {
  name: "RatingInput",
  components: {},

  props: {
    ...inputProps,
    numberOfStars: { type: Number, default: 5 },
  },

  setup(props, context) {
    const formInput = useFormInput(props, context, {
      variants: ratingInputTheme
    })
    return {
      ...formInput,
      props
    }
  },

  data() {
    return {
      hoverRating: -1,
    }
  },

  computed: {
    starsCount() {
      if (!this.numberOfStars || this.numberOfStars < 1) {
        return 5
      }
      return this.numberOfStars
    },
  },

  mounted() {
    if (!this.compVal) this.compVal = 0
  },

  updated() {
    if (!this.compVal) {
      this.compVal = 0
    }
  },

  methods: {
    onMouseHover(i) {
      this.hoverRating = this.disabled ? -1 : i
    },
    setRating(val) {
      if (this.disabled) {
        return
      }
      if (this.compVal === val) {
        this.compVal = 0
      } else {
        this.compVal = val
        if (val && val > 0) {
          this.$emit('input-filled')
        }
      }
    },
    handleKeydown(event, currentStar) {
      if (this.disabled) return

      let nextStar = currentStar

      switch (event.key) {
        case 'ArrowRight':
        case 'ArrowUp':
          event.preventDefault()
          nextStar = Math.min(currentStar + 1, this.starsCount)
          break
        case 'ArrowLeft':
        case 'ArrowDown':
          event.preventDefault()
          nextStar = Math.max(currentStar - 1, 1)
          break
        case 'Enter':
        case ' ':
          event.preventDefault()
          this.setRating(currentStar)
          return
        case 'Home':
          event.preventDefault()
          nextStar = 1
          break
        case 'End':
          event.preventDefault()
          nextStar = this.starsCount
          break
        case '0':
        case 'Backspace':
        case 'Delete':
          event.preventDefault()
          this.setRating(0)
          return
        default: {
          // Handle number keys 1-9
          const num = parseInt(event.key)
          if (num >= 1 && num <= this.starsCount) {
            event.preventDefault()
            this.setRating(num)
            nextStar = num
          } else {
            return
          }
          break
        }
      }

      // Move focus to the next star
      if (nextStar !== currentStar) {
        this.focusOnStar(nextStar)
      }
    },
    focusOnStar(starNumber) {
      // Find the star element and focus it
      this.$nextTick(() => {
        const starElements = this.$el.querySelectorAll('[role="button"]')
        const starElement = starElements[starNumber - 1]
        if (starElement) {
          starElement.focus()
        }
      })
    },
    getStarTabIndex(starNumber) {
      // Make the current value focusable, or first star if no value
      const targetStar = this.compVal || 1
      return starNumber === targetStar ? '0' : '-1'
    },
  },
}
</script>

<style scoped>
.star-value-enter-active,
.star-value-leave-active {
  transition: opacity 0.18s ease, transform 0.18s ease;
}

.star-value-enter-from,
.star-value-leave-to {
  opacity: 0;
  transform: translateY(-2px);
}
</style>
