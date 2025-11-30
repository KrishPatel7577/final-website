(function () {
  function getRelevantImageUrl(productName, category) {
    let prompt = "";
    const nameLower = productName.toLowerCase();

    if (category === 'laptops') prompt = "modern sleek laptop computer on a desk, high quality, photorealistic, 4k";
    else if (category === 'desktops') prompt = "powerful gaming desktop pc with rgb lighting, high quality, photorealistic, 4k";
    else if (category === 'graphic_cards') prompt = "computer graphics card gpu component, high quality, photorealistic, 4k";
    else if (category === 'memories') prompt = "computer ram memory stick component, high quality, photorealistic, 4k";
    else if (category === 'accessories') prompt = "computer accessory tech gadget, high quality, photorealistic, 4k";
    else prompt = `modern ${productName} technology product, high quality, photorealistic, 4k`;

    const encodedPrompt = encodeURIComponent(prompt);
    const seed = Array.from(productName).reduce((acc, char) => acc + char.charCodeAt(0), 0);

    return `https://image.pollinations.ai/prompt/${encodedPrompt}?width=500&height=500&nologo=true&seed=${seed}`;
  }

  function getPicsumUrl(productId) {
    return `https://picsum.photos/seed/${productId}/500/500`;
  }

  function handleImageError(img) {
    if (img.dataset.fallbackCount && parseInt(img.dataset.fallbackCount) > 2) {
      return;
    }

    const currentCount = parseInt(img.dataset.fallbackCount || "0");
    img.dataset.fallbackCount = (currentCount + 1).toString();

    // Get product details
    const productName = img.alt || img.getAttribute("data-product-name") || "Product";
    const productId = img.getAttribute("data-product-id") || "0";
    const category = img.getAttribute("data-category") || "";

    let newSrc;

    if (currentCount === 0) {
      // First attempt: Try Pollinations.ai for relevance
      newSrc = getRelevantImageUrl(productName, category);
    } else if (currentCount === 1) {
      // Second attempt: Fallback to Picsum (reliable but less relevant)
      newSrc = getPicsumUrl(productId);
    } else {
      // Final attempt: Placehold.co (text)
      const encodedName = encodeURIComponent(productName.substring(0, 20));
      newSrc = `https://placehold.co/500x500/e2e8f0/1e293b?text=${encodedName}`;
    }

    // Load the new image
    const testImg = new Image();
    testImg.onload = function () {
      img.src = newSrc;
      img.style.opacity = "1";
      img.classList.add("loaded");
      img.parentElement?.classList.add("loaded");
    };
    testImg.onerror = function () {
      // If this fails, trigger handleImageError again (up to limit)
      handleImageError(img);
    };
    testImg.src = newSrc;
  }

  // Initialize
  document.addEventListener("DOMContentLoaded", function () {
    const images = document.querySelectorAll("img");
    images.forEach((img) => {
      // Ensure opacity is 1 immediately
      img.style.opacity = "1";

      // Attach error handler
      img.onerror = function () {
        handleImageError(this);
      };

      // Check if src is missing, broken placeholder, or empty
      const src = img.getAttribute("src");
      if (!src || src.includes("via.placeholder") || src.includes("placehold.co") || src.trim() === "") {
        handleImageError(img);
      }

      // Check if image failed before script ran
      if (img.complete && img.naturalWidth === 0) {
        handleImageError(img);
      }
    });
  });

  // Observer for dynamic content
  const observer = new MutationObserver((mutations) => {
    mutations.forEach((mutation) => {
      mutation.addedNodes.forEach((node) => {
        if (node.tagName === "IMG") {
          node.style.opacity = "1";
          node.onerror = function () { handleImageError(this); };
          if (!node.src || node.src.includes("placehold.co")) handleImageError(node);
        } else if (node.querySelectorAll) {
          node.querySelectorAll("img").forEach((img) => {
            img.style.opacity = "1";
            img.onerror = function () { handleImageError(this); };
            if (!img.src || img.src.includes("placehold.co")) handleImageError(img);
          });
        }
      });
    });
  });

  observer.observe(document.body, { childList: true, subtree: true });

})();
