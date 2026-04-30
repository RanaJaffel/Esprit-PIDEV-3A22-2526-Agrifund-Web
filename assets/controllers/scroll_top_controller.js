import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static values = { threshold: { type: Number, default: 100 } };

  connect() {
    this.onScroll = this.onScroll.bind(this);
    window.addEventListener("scroll", this.onScroll);
    this.onScroll();
  }

  disconnect() {
    window.removeEventListener("scroll", this.onScroll);
  }

  onScroll() {
    const show = window.scrollY > this.thresholdValue;
    this.element.classList.toggle("active", show);
  }

  toTop(event) {
    event.preventDefault();
    window.scrollTo({ top: 0, behavior: "smooth" });
  }
}
