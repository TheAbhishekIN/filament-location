// Filament Location Package JavaScript

// Global Google Maps API loader singleton
window.GoogleMapsLoader = (function () {
  let isLoading = false;
  let isLoaded = false;
  let loadPromise = null;

  return {
    async load(apiKey) {
      // If already loaded, return immediately
      if (isLoaded && window.google && window.google.maps) {
        return Promise.resolve();
      }

      // If currently loading, return the existing promise
      if (isLoading && loadPromise) {
        return loadPromise;
      }

      // Check if Google Maps is already loaded by another script
      if (window.google && window.google.maps) {
        isLoaded = true;
        return Promise.resolve();
      }

      // Start loading
      isLoading = true;
      loadPromise = new Promise((resolve, reject) => {
        // Check if script tag already exists
        const existingScript = document.querySelector(
          'script[src*="maps.googleapis.com"]'
        );
        if (existingScript) {
          // Wait for existing script to load
          if (window.google && window.google.maps) {
            isLoaded = true;
            isLoading = false;
            resolve();
            return;
          }

          existingScript.onload = () => {
            isLoaded = true;
            isLoading = false;
            resolve();
          };
          existingScript.onerror = (error) => {
            isLoading = false;
            reject(error);
          };
          return;
        }

        // Create unique callback name
        const callbackName = "initGoogleMapsCallback_" + Date.now();

        // Set up global callback
        window[callbackName] = () => {
          isLoaded = true;
          isLoading = false;
          // Clean up callback
          delete window[callbackName];
          resolve();
        };

        // Create new script tag with proper async loading
        const script = document.createElement("script");
        script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&libraries=places,marker&loading=async&callback=${callbackName}`;
        script.async = true;
        script.defer = true;
        script.onerror = (error) => {
          isLoading = false;
          delete window[callbackName];
          reject(error);
        };
        document.head.appendChild(script);
      });

      return loadPromise;
    },

    isReady() {
      return isLoaded && window.google && window.google.maps;
    },
  };
})();

document.addEventListener("alpine:init", () => {
  // Global configuration from PHP - access via Filament's script data
  const config = window.filamentData?.filamentLocationConfig || {};

  // Location Picker Component for Forms
  Alpine.data("locationPicker", (options = {}) => ({
    state: options.state || { latitude: null, longitude: null },
    zoom: options.zoom || config.defaultZoom || 15,
    mapType: options.mapType || "standard",
    showMap: options.showMap !== false,
    showCoordinates: options.showCoordinates !== false,
    mapControls: options.mapControls || {},
    initialLocation: options.initialLocation || {},
    isDisabled: options.isDisabled || false,
    isLoading: false,
    error: null,
    map: null,
    marker: null,

    init() {
      console.log("LocationPicker initializing...");

      // Handle Livewire entanglement errors gracefully
      try {
        // Ensure state is always an object with the correct structure
        if (!this.state || typeof this.state !== "object") {
          this.state = { latitude: null, longitude: null };
        }

        // Ensure state has required properties
        if (!this.state.hasOwnProperty("latitude")) {
          this.state.latitude = null;
        }
        if (!this.state.hasOwnProperty("longitude")) {
          this.state.longitude = null;
        }
      } catch (entangleError) {
        console.warn(
          "Livewire entanglement issue, using local state:",
          entangleError
        );
        this.state = { latitude: null, longitude: null };
      }

      // Load Google Maps API if not already loaded
      this.loadGoogleMapsAPI()
        .then(() => {
          // Initialize with existing location if available
          if (this.initialLocation.latitude && this.initialLocation.longitude) {
            this.state = {
              latitude: this.initialLocation.latitude,
              longitude: this.initialLocation.longitude,
            };
            this.$nextTick(() => {
              if (this.showMap) {
                this.initializeMap();
              }
            });
          }
        })
        .catch((error) => {
          console.error("Failed to load Google Maps API:", error);
          this.error = "Failed to load Google Maps API.";
        });
    },

    async loadGoogleMapsAPI() {
      const apiKey = config.googleMapsApiKey;
      if (!apiKey) {
        this.error = "Google Maps API key is not configured.";
        console.error("No Google Maps API key configured");
        return Promise.reject("No API key");
      }

      return window.GoogleMapsLoader.load(apiKey);
    },

    async getCurrentLocation() {
      console.log("getCurrentLocation called - starting process");

      if (!navigator.geolocation) {
        this.error = "Geolocation is not supported by this browser.";
        console.log("Geolocation not supported, error set to:", this.error);
        return;
      }

      this.isLoading = true;
      this.error = null;
      console.log("Geolocation request starting...");

      const options = {
        enableHighAccuracy: config.enableHighAccuracy || true,
        timeout: config.locationTimeout || 10000,
        maximumAge: 0,
      };

      try {
        const position = await new Promise((resolve, reject) => {
          navigator.geolocation.getCurrentPosition(resolve, reject, options);
        });

        console.log("Geolocation success:", position);

        this.state = {
          latitude: position.coords.latitude,
          longitude: position.coords.longitude,
        };

        this.isLoading = false;

        if (this.showMap) {
          await this.loadGoogleMapsAPI();
          this.$nextTick(() => {
            this.initializeMap();
          });
        }
      } catch (error) {
        console.error("Geolocation error caught:", error);
        this.isLoading = false;
        this.handleLocationError(error);
      }
    },

    clearLocation() {
      this.state = {
        latitude: null,
        longitude: null,
      };
      this.error = null;
      if (this.map) {
        this.map = null;
        this.marker = null;
      }
    },

    initializeMap() {
      if (
        !this.state ||
        !this.state.latitude ||
        !this.state.longitude ||
        !this.$refs.mapContainer
      ) {
        return;
      }

      if (!window.GoogleMapsLoader.isReady()) {
        console.error("Google Maps API not ready");
        return;
      }

      const mapOptions = {
        center: { lat: this.state.latitude, lng: this.state.longitude },
        zoom: this.zoom,
        mapTypeId: this.getGoogleMapType(),
        mapId: "filament-location-map",
        ...this.getMapControlOptions(),
      };

      try {
        this.map = new google.maps.Map(this.$refs.mapContainer, mapOptions);

        const markerPosition = {
          lat: this.state.latitude,
          lng: this.state.longitude,
        };

        if (google.maps.marker && google.maps.marker.AdvancedMarkerElement) {
          this.marker = new google.maps.marker.AdvancedMarkerElement({
            map: this.map,
            position: markerPosition,
            title: "Selected Location",
            gmpDraggable: !this.isDisabled,
          });

          if (!this.isDisabled) {
            this.marker.addListener("dragend", (event) => {
              this.state = {
                latitude: event.latLng.lat(),
                longitude: event.latLng.lng(),
              };
            });
          }
        } else {
          this.marker = new google.maps.Marker({
            position: markerPosition,
            map: this.map,
            title: "Selected Location",
            draggable: !this.isDisabled,
          });

          if (!this.isDisabled) {
            this.marker.addListener("dragend", (event) => {
              this.state = {
                latitude: event.latLng.lat(),
                longitude: event.latLng.lng(),
              };
            });
          }
        }
      } catch (error) {
        console.error("Error creating map:", error);
        this.error = "Failed to create map.";
      }
    },

    getGoogleMapType() {
      switch (this.mapType) {
        case "satellite":
          return google.maps.MapTypeId.SATELLITE;
        case "hybrid":
          return google.maps.MapTypeId.HYBRID;
        case "terrain":
          return google.maps.MapTypeId.TERRAIN;
        default:
          return google.maps.MapTypeId.ROADMAP;
      }
    },

    getMapControlOptions() {
      return {
        zoomControl: this.mapControls.zoom_control !== false,
        mapTypeControl: this.mapControls.map_type_control !== false,
        scaleControl: this.mapControls.scale_control !== false,
        streetViewControl: this.mapControls.street_view_control !== false,
        rotateControl: this.mapControls.rotate_control !== false,
        fullscreenControl: this.mapControls.fullscreen_control !== false,
      };
    },

    handleLocationError(error) {
      console.error("=== HANDLING LOCATION ERROR ===");
      console.error("Error code:", error.code);
      console.error("Error message:", error.message);
      console.error("Full error object:", error);

      // Clear any existing error first
      this.error = null;

      // Use setTimeout instead of $nextTick for more reliable reactivity
      setTimeout(() => {
        let errorMessage = "";

        // Check for HTTPS requirement first (this is most common)
        if (
          error.message &&
          (error.message.includes("secure origins") ||
            error.message.includes("Only secure origins are allowed") ||
            error.message.includes("https://goo.gl/Y0ZkNV"))
        ) {
          errorMessage =
            "🔒 Location access requires HTTPS. Please use HTTPS or localhost.";
        } else {
          // Handle other error codes
          switch (error.code) {
            case 1: // PERMISSION_DENIED
              errorMessage =
                "🚫 Location access was denied. Please allow location permission and try again.";
              break;
            case 2: // POSITION_UNAVAILABLE
              errorMessage =
                "📍 Location information is unavailable. Please check your device settings.";
              break;
            case 3: // TIMEOUT
              errorMessage = "⏱️ Location request timed out. Please try again.";
              break;
            default:
              errorMessage =
                "❌ Unable to get location: " +
                (error.message || "Unknown error");
              break;
          }
        }

        this.error = errorMessage;
        console.log("Error message set to:", this.error);

        // Force Alpine to update the DOM
        this.$nextTick(() => {
          console.log("DOM should be updated with error:", this.error);
        });
      }, 10);
    },

    get formattedCoordinates() {
      if (this.state && this.state.latitude && this.state.longitude) {
        return `${this.state.latitude.toFixed(
          6
        )}, ${this.state.longitude.toFixed(6)}`;
      }
      return "No coordinates";
    },

    get hasValidState() {
      return this.state && typeof this.state === "object";
    },

    get hasLocation() {
      return this.hasValidState && this.state.latitude && this.state.longitude;
    },

    get canClearLocation() {
      return (
        this.hasValidState && (this.state.latitude || this.state.longitude)
      );
    },
  }));

  // Location Column Component for Tables
  Alpine.data("locationColumn", (options = {}) => ({
    latitude: options.latitude || null,
    longitude: options.longitude || null,
    zoom: options.zoom || config.defaultZoom || 15,
    mapType: options.mapType || "standard",
    showModal: false,
    map: null,
    marker: null,

    openLocationModal() {
      this.showModal = true;
      this.$nextTick(() => {
        this.initializeModalMap();
      });
    },

    closeLocationModal() {
      this.showModal = false;
    },

    async loadGoogleMapsAPI() {
      const apiKey = config.googleMapsApiKey;
      if (!apiKey) {
        console.error("Google Maps API key is not configured.");
        return Promise.reject("No API key");
      }

      return window.GoogleMapsLoader.load(apiKey);
    },

    async initializeModalMap() {
      if (!this.latitude || !this.longitude) {
        return;
      }

      try {
        await this.loadGoogleMapsAPI();

        const mapContainer = this.$refs.modalMapContainer;
        if (!mapContainer) return;

        if (!window.GoogleMapsLoader.isReady()) {
          console.error("Google Maps API not ready");
          return;
        }

        const mapOptions = {
          center: {
            lat: parseFloat(this.latitude),
            lng: parseFloat(this.longitude),
          },
          zoom: this.zoom,
          mapTypeId: this.getGoogleMapType(),
          ...this.getMapControlOptions(),
        };

        this.map = new google.maps.Map(mapContainer, mapOptions);

        const markerPosition = {
          lat: parseFloat(this.latitude),
          lng: parseFloat(this.longitude),
        };

        this.marker = new google.maps.Marker({
          position: markerPosition,
          map: this.map,
          title: "Location",
          animation: google.maps.Animation.DROP,
        });
      } catch (error) {
        console.error("Failed to load Google Maps:", error);
      }
    },

    openInGoogleMaps() {
      const url = `https://www.google.com/maps?q=${this.latitude},${this.longitude}`;
      window.open(url, "_blank");
    },

    getGoogleMapType() {
      switch (this.mapType) {
        case "satellite":
          return google.maps.MapTypeId.SATELLITE;
        case "hybrid":
          return google.maps.MapTypeId.HYBRID;
        case "terrain":
          return google.maps.MapTypeId.TERRAIN;
        default:
          return google.maps.MapTypeId.ROADMAP;
      }
    },

    getMapControlOptions() {
      return {
        zoomControl: true,
        mapTypeControl: true,
        scaleControl: true,
        streetViewControl: true,
        rotateControl: true,
        fullscreenControl: true,
      };
    },

    get hasLocation() {
      return this.latitude && this.longitude;
    },

    get formattedCoordinates() {
      if (this.hasLocation) {
        return `${parseFloat(this.latitude).toFixed(6)}, ${parseFloat(
          this.longitude
        ).toFixed(6)}`;
      }
      return "No location";
    },
  }));
});
