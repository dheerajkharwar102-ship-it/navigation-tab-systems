<style>
   .order-product-wrapper {
      background: white;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      overflow: hidden;
      margin-bottom: 20px;
      border: 1px solid #e0e0e0;
   }

   .nav-tabs {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      padding: 8px 12px 0;
      border-bottom: none;
      display: flex;
      align-items: center;
      min-height: 40px;
   }

   .nav-tabs .nav-link {
      border: none;
      border-radius: 6px 6px 0 0;
      padding: 6px 12px;
      color: rgba(255, 255, 255, 0.9);
      font-weight: 500;
      font-size: 0.85rem;
      transition: all 0.2s ease;
      position: relative;
      margin-right: 4px;
      background: rgba(255, 255, 255, 0.15);
      backdrop-filter: blur(8px);
   }

   .nav-tabs .nav-link:hover {
      background: rgba(255, 255, 255, 0.25);
      color: white;
      transform: translateY(-1px);
   }

   .nav-tabs .nav-link.active {
      background: white;
      color: #4361ee;
      font-weight: 600;
      box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.1);
   }

   .room-header {
      display: flex;
      align-items: center;
      gap: 6px;
   }

   .close-room {
      color: rgba(255, 255, 255, 0.8);
      transition: all 0.2s ease;
      padding: 4px;
      border-radius: 4px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.75rem;
   }

   .close-room:hover {
      color: white;
      background: rgba(255, 255, 255, 0.2);
   }

   .nav-tabs .nav-link.active .close-room {
      color: #6c757d;
   }

   .nav-tabs .nav-link.active .close-room:hover {
      color: #dc3545;
      background: rgba(220, 53, 69, 0.1);
   }

   .tab-content {
      padding: 0;
   }

   .tab-pane {
      padding: 0;
   }

   .product-tabs-wrapper {
      display: flex;
      flex-direction: column;
      height: 100%;
      min-height: 400px;
   }

   .product-tabs-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 12px 16px;
      border-bottom: 1px solid #e0e0e0;
      background: #f8f9fa;
      min-height: 36px;
   }

   .room-info-form {
      display: flex;
      align-items: center;
      gap: 16px;
      flex: 1;
   }

   .form-group-small {
      margin: 0;
      min-width: 120px;
   }

   .form-group-small label {
      font-size: 0.75rem;
      font-weight: 500;
      color: #495057;
      margin-bottom: 4px;
      display: block;
   }

   .form-control-small {
      height: 28px;
      font-size: 0.8rem;
      padding: 4px 8px;
      border: 1px solid #e0e0e0;
      border-radius: 4px;
      width: 100%;
   }

   .form-control-small:focus {
      border-color: #4361ee;
      box-shadow: 0 0 0 0.1rem rgba(67, 97, 238, 0.15);
   }

   .image-upload-container {
      display: flex;
      align-items: center;
      gap: 8px;
   }

   .image-preview {
      width: 32px;
      height: 32px;
      border-radius: 4px;
      border: 1px solid #e0e0e0;
      background: #f8f9fa;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
   }

   .image-preview img {
      width: 100%;
      height: 100%;
      object-fit: cover;
   }

   .image-preview i {
      color: #6c757d;
      font-size: 0.8rem;
   }

   .file-input-wrapper {
      position: relative;
      overflow: hidden;
      display: inline-block;
   }

   .file-input-wrapper input[type=file] {
      position: absolute;
      left: 0;
      top: 0;
      opacity: 0;
      width: 100%;
      height: 100%;
      cursor: pointer;
   }

   .product-tabs-container {
      display: flex;
      gap: 4px;
      padding: 8px 12px;
      background: #f8f9fa;
      border-bottom: 1px solid #e0e0e0;
      flex-wrap: wrap;
      min-height: 48px;
      align-items: flex-start;
   }

   .product-tab {
      display: flex;
      align-items: center;
      gap: 6px;
      padding: 4px 8px;
      background: white;
      border: 1px solid #dee2e6;
      border-radius: 4px;
      cursor: pointer;
      transition: all 0.2s ease;
      min-width: 100px;
      position: relative;
      font-size: 0.8rem;
   }

   .product-tab:hover {
      border-color: #4361ee;
      transform: translateY(-1px);
      box-shadow: 0 1px 4px rgba(67, 97, 238, 0.15);
   }

   .product-tab.active {
      border-color: #4361ee;
      background: linear-gradient(135deg, #4361ee08 0%, #3a0ca308 100%);
      box-shadow: 0 2px 6px rgba(67, 97, 238, 0.2);
   }

   .product-tab-icon {
      width: 20px;
      height: 20px;
      border-radius: 4px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.7rem;
      color: white;
   }

   .product-tab-name {
      font-size: 0.8rem;
      font-weight: 500;
      color: #495057;
      flex: 1;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
   }

   .product-tab-close {
      width: 16px;
      height: 16px;
      border-radius: 3px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #6c757d;
      transition: all 0.2s ease;
      font-size: 0.65rem;
   }

   .product-tab-close:hover {
      background: rgba(220, 53, 69, 0.1);
      color: #dc3545;
   }

   .product-content-area {
      flex: 1;
      padding: 0;
      background: white;
      overflow: hidden;
   }

   .product-content {
      height: 100%;
      display: none;
   }

   .product-content.active {
      display: block;
   }

   .add-item-to-room-btn {
      background: linear-gradient(135deg, #4361ee, #3a0ca3);
      border: none;
      border-radius: 4px;
      padding: 6px 12px;
      color: white;
      font-size: 0.8rem;
      font-weight: 500;
      transition: all 0.2s ease;
      display: flex;
      align-items: center;
      gap: 4px;
      white-space: nowrap;
   }

   .add-item-to-room-btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 2px 6px rgba(67, 97, 238, 0.3);
   }

   .compact-product-details {
      background: white;
      border-radius: 6px;
      padding: 12px 16px;
      border: 1px solid #e0e0e0;
      margin-bottom: 16px;
   }

   .compact-details-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
      gap: 16px;
      align-items: end;
   }

   .compact-detail-group {
      display: flex;
      flex-direction: column;
      gap: 4px;
   }

   .compact-detail-group label {
      font-size: 0.75rem;
      font-weight: 500;
      color: #495057;
      margin-bottom: 0;
   }

   .compact-detail-group input {
      height: 28px;
      font-size: 0.8rem;
      padding: 4px 8px;
      border: 1px solid #e0e0e0;
      border-radius: 4px;
      width: 100%;
   }

   .compact-detail-group input:focus {
      border-color: #4361ee;
      box-shadow: 0 0 0 0.1rem rgba(67, 97, 238, 0.15);
   }

   .compact-detail-group input[readonly] {
      background-color: #f8f9fa;
      cursor: not-allowed;
   }

   .compact-section-header {
      display: flex;
      align-items: center;
      margin-bottom: 8px;
      padding-bottom: 8px;
      border-bottom: 1px solid #f8f9fa;
   }

   .compact-section-header h6 {
      margin: 0;
      color: #495057;
      font-weight: 600;
      font-size: 0.9rem;
   }

   .product-header-section {
      display: none;
   }

   .complex-product-layout {
      display: grid;
      grid-template-columns: 250px 1fr;
      gap: 16px;
      height: 100%;
      min-height: 500px;
   }

   .item-details-content {
      background: white;
      border-radius: 6px;
      border: 1px solid #e0e0e0;
      height: 100%;
      display: flex;
      flex-direction: column;
   }

   .item-details-header {
      padding: 8px 16px;
      border-bottom: 1px solid #e0e0e0;
      background: #f8f9fa;
      border-radius: 6px 6px 0 0;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 8px;
   }

   .item-details-header h6 {
      margin: 0;
      color: #495057;
      font-weight: 600;
      font-size: 0.9rem;
      white-space: nowrap;
   }

   .compact-header-details {
      display: flex;
      align-items: center;
      gap: 12px;
      flex-wrap: wrap;
   }

   .compact-header-group {
      display: flex;
      align-items: center;
      gap: 4px;
   }

   .compact-header-group label {
      font-size: 0.7rem;
      font-weight: 500;
      color: #495057;
      margin: 0;
      white-space: nowrap;
   }

   .compact-header-group input {
      height: 24px;
      font-size: 0.75rem;
      padding: 2px 6px;
      border: 1px solid #e0e0e0;
      border-radius: 4px;
      width: 70px;
   }

   .compact-header-group input:focus {
      border-color: #4361ee;
      box-shadow: 0 0 0 0.1rem rgba(67, 97, 238, 0.15);
   }

   .compact-header-group input[readonly] {
      background-color: #f8f9fa;
      cursor: not-allowed;
   }

   .item-details-body {
      flex: 1;
      padding: 16px;
      overflow-y: auto;
      display: flex;
      flex-direction: column;
   }

   .items-tabs-sidebar {
      background: #f8f9fa;
      border-radius: 6px;
      border: 1px solid #e0e0e0;
      display: flex;
      flex-direction: column;
      height: 100%;
   }

   .items-tabs-header {
      padding: 12px;
      border-bottom: 1px solid #e0e0e0;
      background: white;
      border-radius: 6px 6px 0 0;
      display: flex;
      justify-content: space-between;
      align-items: center;
   }

   .items-tabs-header h6 {
      margin: 0;
      color: #495057;
      font-weight: 600;
      font-size: 0.9rem;
   }

   .items-tabs-container {
      flex: 1;
      overflow-y: auto;
      padding: 8px;
   }

   .items-tab {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 8px 12px;
      border-radius: 4px;
      cursor: pointer;
      transition: all 0.2s ease;
      margin-bottom: 4px;
      border: 1px solid transparent;
   }

   .items-tab:hover {
      background: #e9ecef;
   }

   .items-tab.active {
      background: white;
      border-color: #4361ee;
      box-shadow: 0 1px 3px rgba(67, 97, 238, 0.2);
   }

   .items-tab-name {
      font-weight: 500;
      color: #495057;
      font-size: 0.8rem;
      flex: 1;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
   }

   .items-tab-close {
      width: 16px;
      height: 16px;
      border-radius: 3px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #6c757d;
      transition: all 0.2s ease;
      font-size: 0.65rem;
      opacity: 0;
   }

   .items-tab:hover .items-tab-close {
      opacity: 1;
   }

   .items-tab-close:hover {
      background: rgba(220, 53, 69, 0.1);
      color: #dc3545;
   }

   .add-item-section {
      padding: 12px;
      border-top: 1px solid #e0e0e0;
      background: white;
      border-radius: 0 0 6px 0;
   }

   .empty-item-selection {
      text-align: center;
      padding: 40px 20px;
      color: #6c757d;
   }

   .empty-item-selection i {
      font-size: 3rem;
      margin-bottom: 12px;
      color: #adb5bd;
      opacity: 0.6;
   }

   .empty-item-selection p {
      margin: 0 0 16px 0;
      font-size: 0.9rem;
   }

   .empty-items-tabs {
      text-align: center;
      padding: 40px 20px;
      color: #6c757d;
   }

   .empty-items-tabs i {
      font-size: 2.5rem;
      margin-bottom: 12px;
      color: #adb5bd;
      opacity: 0.6;
   }

   .empty-items-tabs p {
      margin: 0 0 16px 0;
      font-size: 0.85rem;
   }

   .product-dimensions {
      background: white;
      border-radius: 6px;
      padding: 12px;
      border: 1px solid #e0e0e0;
   }

   .product-dimensions h6 {
      margin: 0 0 8px 0;
      color: #495057;
      font-weight: 600;
      font-size: 0.9rem;
   }

   .dimensions-inputs {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 8px;
   }

   .dimension-input {
      display: flex;
      flex-direction: column;
      gap: 4px;
   }

   .dimension-input label {
      font-size: 0.75rem;
      font-weight: 500;
      color: #495057;
   }

   .dimension-input input {
      height: 28px;
      font-size: 0.8rem;
      padding: 4px 8px;
      border: 1px solid #e0e0e0;
      border-radius: 4px;
   }

   .dimension-input input:focus {
      border-color: #4361ee;
      box-shadow: 0 0 0 0.1rem rgba(67, 97, 238, 0.15);
   }

   .product-price {
      background: white;
      border-radius: 6px;
      padding: 12px;
      border: 1px solid #e0e0e0;
   }

   .product-price h6 {
      margin: 0 0 8px 0;
      color: #495057;
      font-weight: 600;
      font-size: 0.9rem;
   }

   .price-inputs {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 8px;
   }

   .price-input {
      display: flex;
      flex-direction: column;
      gap: 4px;
   }

   .price-input label {
      font-size: 0.75rem;
      font-weight: 500;
      color: #495057;
   }

   .price-input input {
      height: 28px;
      font-size: 0.8rem;
      padding: 4px 8px;
      border: 1px solid #e0e0e0;
      border-radius: 4px;
   }

   .price-input input:focus {
      border-color: #4361ee;
      box-shadow: 0 0 0 0.1rem rgba(67, 97, 238, 0.15);
   }

   .enhanced-category-item {
      background: white;
      border-radius: 6px;
      padding: 12px;
      margin-bottom: 8px;
      border: 1px solid #e0e0e0;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
   }

   .enhanced-item-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 8px;
      padding-bottom: 8px;
      border-bottom: 1px solid #f8f9fa;
   }

   .enhanced-item-name {
      font-weight: 600;
      color: #495057;
      font-size: 0.9rem;
   }

   .enhanced-item-details {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
   }

   .detail-group {
      display: flex;
      flex-direction: column;
      gap: 4px;
   }

   .detail-group label {
      font-size: 0.75rem;
      font-weight: 500;
      color: #495057;
   }

   .detail-group input,
   .detail-group textarea {
      font-size: 0.8rem;
      padding: 6px 8px;
      border: 1px solid #e0e0e0;
      border-radius: 4px;
   }

   .detail-group input:focus,
   .detail-group textarea:focus {
      border-color: #4361ee;
      box-shadow: 0 0 0 0.1rem rgba(67, 97, 238, 0.15);
   }

   .enhanced-item-actions {
      display: flex;
      justify-content: flex-end;
      gap: 8px;
      margin-top: 8px;
   }

   .btn-icon {
      width: 28px;
      height: 28px;
      padding: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      border: none;
      border-radius: 4px;
      transition: all 0.2s ease;
      font-size: 0.75rem;
   }

   .remove-item {
      color: #6c757d;
      background: transparent;
   }

   .remove-item:hover {
      color: #dc3545;
      background: rgba(220, 53, 69, 0.1);
   }

   .btn-primary {
      background: linear-gradient(135deg, #4361ee, #3a0ca3);
      border: none;
      border-radius: 4px;
      font-weight: 500;
      font-size: 0.8rem;
      padding: 6px 12px;
      transition: all 0.2s ease;
   }

   .btn-primary:hover {
      transform: translateY(-1px);
      box-shadow: 0 2px 6px rgba(67, 97, 238, 0.3);
   }

   .btn-outline-primary {
      color: #4361ee;
      border-color: #4361ee;
      border-radius: 4px;
      font-weight: 500;
      font-size: 0.8rem;
      padding: 6px 12px;
      transition: all 0.2s ease;
   }

   .btn-outline-primary:hover {
      background: #4361ee;
      border-color: #4361ee;
      transform: translateY(-1px);
   }

   .btn-secondary {
      background: #6c757d;
      border: none;
      border-radius: 4px;
      font-weight: 500;
      font-size: 0.8rem;
      padding: 6px 12px;
      transition: all 0.2s ease;
   }

   .btn-secondary:hover {
      background: #5a6268;
      transform: translateY(-1px);
   }

   .status-indicator {
      display: inline-block;
      width: 8px;
      height: 8px;
      border-radius: 50%;
      margin-right: 6px;
   }

   .status-complete {
      background: linear-gradient(135deg, #2a9d8f, #2ec4b6);
   }

   .status-incomplete {
      background: linear-gradient(135deg, #ffb703, #ff9e00);
   }

   .status-empty {
      background: linear-gradient(135deg, #adb5bd, #6c757d);
   }

   .product-empty-state {
      text-align: center;
      margin: auto;
      padding: 20px 16px;
      color: #6c757d;
   }

   .product-empty-state i {
      font-size: 2rem;
      margin-bottom: 8px;
      color: #adb5bd;
      opacity: 0.6;
   }

   .product-empty-state p {
      margin: 0 0 12px 0;
      font-size: 0.85rem;
   }

   .loading-state {
      text-align: center;
      padding: 20px 16px;
      color: #6c757d;
   }

   .loading-state i {
      font-size: 1.5rem;
      margin-bottom: 8px;
      color: #4361ee;
   }

   .loading-state p {
      margin: 0;
      font-size: 0.85rem;
   }

   /* Material section styles */
   .material-section {
      background: white;
      border-radius: 6px;
      padding: 12px;
      border: 1px solid #e0e0e0;
      margin-top: 12px;
   }

   .material-section h6 {
      margin: 0 0 8px 0;
      color: #495057;
      font-weight: 600;
      font-size: 0.9rem;
   }

   /* Updated Material Layout - Image on left, details on right */
   .material-inputs-compact {
      display: grid;
      grid-template-columns: 160px 1fr;
      gap: 16px;
      align-items: start;
   }

   .material-input {
      display: flex;
      flex-direction: column;
      gap: 4px;
   }

   .material-input label {
      font-size: 0.75rem;
      font-weight: 500;
      color: #495057;
   }

   .material-input input,
   .material-input select {
      font-size: 0.8rem;
      padding: 6px 8px;
      border: 1px solid #e0e0e0;
      border-radius: 4px;
   }

   .material-input input:focus,
   .material-input select:focus {
      border-color: #4361ee;
      box-shadow: 0 0 0 0.1rem rgba(67, 97, 238, 0.15);
   }

   /* Product image layout - UPDATED SIZES */
   .compact-image-preview {
      width: 160px;
      height: 160px;
      border-radius: 6px;
      border: 1px solid #e0e0e0;
      background: #f8f9fa;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
   }

   .compact-image-preview img {
      width: 100%;
      height: 100%;
      object-fit: cover;
   }

   .compact-image-preview i {
      color: #6c757d;
      font-size: 2rem;
   }

   .material-compact-image {
      width: 160px;
      height: 160px;
      border-radius: 6px;
      border: 1px solid #e0e0e0;
      background: #f8f9fa;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
   }

   .material-compact-image img {
      width: 100%;
      height: 100%;
      object-fit: cover;
   }

   .material-compact-image i {
      color: #6c757d;
      font-size: 2rem;
   }

   .material-compact-fields {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
   }

   /* Enhanced item layout - UPDATED SIZES */
   .enhanced-image-preview {
      width: 200px;
      height: 200px;
      border-radius: 6px;
      border: 1px solid #e0e0e0;
      background: #f8f9fa;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
   }

   .enhanced-image-preview img {
      width: 100%;
      height: 100%;
      object-fit: cover;
   }

   .enhanced-image-preview i {
      color: #6c757d;
      font-size: 2.5rem;
   }

   /* Material tabs styling */
   .material-tabs {
      display: flex;
      background: #f8f9fa;
      border-bottom: 1px solid #e0e0e0;
      padding: 0 12px;
      overflow-x: auto;
   }

   .material-tab {
      padding: 8px 16px;
      border: none;
      background: transparent;
      color: #6c757d;
      font-size: 0.8rem;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.2s ease;
      border-bottom: 2px solid transparent;
      white-space: nowrap;
   }

   .material-tab:hover {
      color: #4361ee;
   }

   .material-tab.active {
      color: #4361ee;
      border-bottom-color: #4361ee;
      background: rgba(67, 97, 238, 0.05);
   }

   .material-tab-content {
      padding: 16px;
      display: none;
   }

   .material-tab-content.active {
      display: block;
   }

   /* Pillow subcategories section with horizontal tabs */
   .pillow-subcategories-section {
      background: white;
      border-radius: 6px;
      padding: 12px;
      border: 1px solid #e0e0e0;
      margin-top: 12px;
   }

   .pillow-subcategories-section h6 {
      margin: 0 0 12px 0;
      color: #495057;
      font-weight: 600;
      font-size: 0.9rem;
   }

   .pillow-subcategories-tabs {
      display: flex;
      background: #f8f9fa;
      border-radius: 6px;
      padding: 8px;
      gap: 4px;
      margin-bottom: 16px;
      border: 1px solid #e0e0e0;
   }

   .pillow-subcategory-tab {
      flex: 1;
      padding: 8px 12px;
      border: none;
      background: transparent;
      color: #6c757d;
      font-size: 0.75rem;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.2s ease;
      border-radius: 4px;
      text-align: center;
      white-space: nowrap;
   }

   .pillow-subcategory-tab:hover {
      background: #e9ecef;
      color: #495057;
   }

   .pillow-subcategory-tab.active {
      background: #4361ee;
      color: white;
      box-shadow: 0 1px 3px rgba(67, 97, 238, 0.3);
   }

   .pillow-subcategory-content {
      display: none;
   }

   .pillow-subcategory-content.active {
      display: block;
   }

   .pillow-subcategory-details {
      background: #f8f9fa;
      border-radius: 6px;
      padding: 16px;
      border: 1px solid #e0e0e0;
   }

   /* Updated layouts to accommodate larger images */
   .compact-details-with-image {
      display: grid;
      grid-template-columns: 160px 1fr;
      gap: 16px;
      align-items: start;
   }

   .enhanced-details-with-image {
      display: grid;
      grid-template-columns: 200px 1fr;
      gap: 16px;
      align-items: start;
   }

   .product-details-header {
      padding: 8px 16px;
      border-bottom: 1px solid #e0e0e0;
      background: #f8f9fa;
      display: flex;
      justify-content: space-between;
      align-items: center;
   }

   .product-details-header h6 {
      margin: 0;
      color: #495057;
      font-weight: 600;
      font-size: 0.9rem;
   }

   .item-details-subheader {
      padding: 8px 16px;
      border-bottom: 1px solid #e0e0e0;
      background: #f8f9fa;
   }

   .item-details-subheader h6 {
      margin: 0;
      color: #495057;
      font-weight: 600;
      font-size: 0.9rem;
   }

   .product-details-body {
      flex: 1;
      padding: 16px;
      overflow-y: auto;
      display: flex;
      flex-direction: column;
   }

   .product-details-content {
      display: flex;
      flex-direction: column;
      gap: 16px;
   }

   .compact-details-fields {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
      gap: 12px;
   }

   .enhanced-details-fields {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
      gap: 12px;
   }

   .product-header-with-image {
      display: flex;
      align-items: center;
      gap: 12px;
   }

   .header-image-preview {
      width: 40px;
      height: 40px;
      border-radius: 4px;
      border: 1px solid #e0e0e0;
      background: #f8f9fa;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
   }

   .header-image-preview img {
      width: 100%;
      height: 100%;
      object-fit: cover;
   }

   .header-image-preview i {
      color: #6c757d;
      font-size: 1rem;
   }

   /* Modal Styles */
   .qualification-modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      z-index: 1050;
   }

   .qualification-modal-content {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      background: white;
      border-radius: 8px;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
      width: 90%;
      max-width: 500px;
      max-height: 70vh;
      overflow: hidden;
   }

   .qualification-modal-header {
      padding: 12px 16px;
      border-bottom: 1px solid #e0e0e0;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
   }

   .qualification-modal-header h5 {
      margin: 0;
      font-weight: 600;
      font-size: 1rem;
   }

   .search-container {
      padding: 8px 16px 0;
   }

   .search-input {
      width: 100%;
      padding: 8px 12px;
      border: 1px solid #e0e0e0;
      border-radius: 4px;
      font-size: 0.8rem;
   }

   .search-input:focus {
      border-color: #4361ee;
      box-shadow: 0 0 0 0.1rem rgba(67, 97, 238, 0.15);
   }

   .qualification-modal-body {
      padding: 12px 16px;
      max-height: 50vh;
      overflow-y: auto;
   }

   .qualification-options {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 8px;
   }

   .qualification-option {
      border: 1px solid #dee2e6;
      border-radius: 6px;
      padding: 12px;
      cursor: pointer;
      transition: all 0.2s ease;
      background: white;
   }

   .qualification-option:hover {
      border-color: #4361ee;
      transform: translateY(-1px);
      box-shadow: 0 2px 8px rgba(67, 97, 238, 0.15);
   }

   .qualification-option.selected {
      border-color: #4361ee;
      background: linear-gradient(135deg, #4361ee08 0%, #3a0ca308 100%);
      box-shadow: 0 2px 8px rgba(67, 97, 238, 0.2);
   }

   .qualification-option-header {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 8px;
   }

   .qualification-option-icon {
      width: 32px;
      height: 32px;
      border-radius: 6px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 0.9rem;
   }

   .qualification-option-name {
      font-weight: 600;
      color: #495057;
      font-size: 0.9rem;
   }

   .qualification-option-description {
      color: #6c757d;
      font-size: 0.75rem;
      line-height: 1.3;
   }

   .qualification-modal-footer {
      padding: 8px 16px;
      border-top: 1px solid #e0e0e0;
      display: flex;
      justify-content: flex-end;
      gap: 8px;
      background: #f8f9fa;
   }

   .multi-select-modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      z-index: 1070;
   }

   .multi-select-modal-content {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      background: white;
      border-radius: 8px;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
      width: 90%;
      max-width: 800px;
      max-height: 80vh;
      overflow: hidden;
   }

   .multi-select-modal-header {
      padding: 12px 16px;
      border-bottom: 1px solid #e0e0e0;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
   }

   .multi-select-modal-header h5 {
      margin: 0;
      font-weight: 600;
      font-size: 1rem;
   }

   .multi-select-modal-body {
      padding: 12px 16px;
      max-height: 60vh;
      overflow-y: auto;
   }

   .multi-select-options {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
      gap: 12px;
   }

   .multi-select-option {
      border: 1px solid #e0e0e0;
      border-radius: 6px;
      padding: 12px;
      cursor: pointer;
      transition: all 0.2s ease;
      background: white;
   }

   .multi-select-option:hover {
      border-color: #4361ee;
      transform: translateY(-1px);
      box-shadow: 0 2px 8px rgba(67, 97, 238, 0.15);
   }

   .multi-select-option.selected {
      border-color: #4361ee;
      background: linear-gradient(135deg, #4361ee08 0%, #3a0ca308 100%);
      box-shadow: 0 2px 8px rgba(67, 97, 238, 0.2);
   }

   .multi-select-option-header {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 8px;
   }

   .multi-select-option-icon {
      width: 32px;
      height: 32px;
      border-radius: 6px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 0.9rem;
   }

   .multi-select-option-name {
      font-weight: 600;
      color: #495057;
      font-size: 0.9rem;
   }

   .multi-select-option-description {
      color: #6c757d;
      font-size: 0.75rem;
      line-height: 1.3;
   }

   .multi-select-modal-footer {
      padding: 8px 16px;
      border-top: 1px solid #e0e0e0;
      display: flex;
      justify-content: flex-end;
      gap: 8px;
      background: #f8f9fa;
   }

   .item-selection-modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      z-index: 1060;
   }

   .item-selection-modal-content {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      background: white;
      border-radius: 8px;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
      width: 90%;
      max-width: 700px;
      max-height: 80vh;
      overflow: hidden;
   }

   .item-selection-modal-header {
      padding: 12px 16px;
      border-bottom: 1px solid #e0e0e0;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
   }

   .item-selection-modal-header h5 {
      margin: 0;
      font-weight: 600;
      font-size: 1rem;
   }

   .item-selection-modal-body {
      padding: 12px 16px;
      max-height: 60vh;
      overflow-y: auto;
   }

   .item-categories {
      display: grid;
      grid-template-columns: 200px 1fr;
      gap: 16px;
      height: 400px;
   }

   .item-categories-sidebar {
      background: #f8f9fa;
      border-radius: 6px;
      padding: 8px;
   }

   .item-category-tabs {
      display: flex;
      flex-direction: column;
      gap: 4px;
   }

   .item-category-tab {
      padding: 8px 12px;
      border-radius: 4px;
      cursor: pointer;
      transition: all 0.2s ease;
      font-size: 0.8rem;
      font-weight: 500;
   }

   .item-category-tab:hover {
      background: #e9ecef;
   }

   .item-category-tab.active {
      background: #4361ee;
      color: white;
   }

   .item-category-content {
      flex: 1;
   }

   .item-options {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
      gap: 8px;
      max-height: 350px;
      overflow-y: auto;
   }

   .item-option {
      border: 1px solid #e0e0e0;
      border-radius: 6px;
      padding: 12px;
      cursor: pointer;
      transition: all 0.2s ease;
      text-align: center;
      background: white;
   }

   .item-option:hover {
      border-color: #4361ee;
      transform: translateY(-1px);
      box-shadow: 0 2px 8px rgba(67, 97, 238, 0.15);
   }

   .item-option.selected {
      border-color: #4361ee;
      background: linear-gradient(135deg, #4361ee08 0%, #3a0ca308 100%);
      box-shadow: 0 2px 8px rgba(67, 97, 238, 0.2);
   }

   .item-option-icon {
      width: 40px;
      height: 40px;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 8px;
      color: white;
      font-size: 1rem;
   }

   .item-option-name {
      font-weight: 500;
      color: #495057;
      font-size: 0.8rem;
      margin-bottom: 4px;
   }

   .item-option-description {
      color: #6c757d;
      font-size: 0.7rem;
      line-height: 1.2;
   }

   .item-selection-modal-footer {
      padding: 8px 16px;
      border-top: 1px solid #e0e0e0;
      display: flex;
      justify-content: flex-end;
      gap: 8px;
      background: #f8f9fa;
   }

   /* Additional styles for curtain options */
   .curtain-options-section {
      background: white;
      border-radius: 6px;
      padding: 12px;
      border: 1px solid #e0e0e0;
      margin-top: 12px;
   }

   .curtain-options-section h6 {
      margin: 0 0 12px 0;
      color: #495057;
      font-weight: 600;
      font-size: 0.9rem;
   }

   .curtain-controls {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
      margin-bottom: 16px;
   }

   .curtain-control {
      display: flex;
      flex-direction: column;
      gap: 4px;
   }

   .curtain-control label {
      font-size: 0.75rem;
      font-weight: 500;
      color: #495057;
   }

   .curtain-control select {
      font-size: 0.8rem;
      padding: 6px 8px;
      border: 1px solid #e0e0e0;
      border-radius: 4px;
   }

   .curtain-control select:focus {
      border-color: #4361ee;
      box-shadow: 0 0 0 0.1rem rgba(67, 97, 238, 0.15);
   }

   /* Accessory section layout - same as items section */
   .accessory-layout {
      display: grid;
      grid-template-columns: 250px 1fr;
      gap: 16px;
      height: 100%;
      min-height: 400px;
   }

   .accessory-tabs-sidebar {
      background: #f8f9fa;
      border-radius: 6px;
      border: 1px solid #e0e0e0;
      display: flex;
      flex-direction: column;
      height: 100%;
   }

   .accessory-tabs-header {
      padding: 12px;
      border-bottom: 1px solid #e0e0e0;
      background: white;
      border-radius: 6px 6px 0 0;
      display: flex;
      justify-content: space-between;
      align-items: center;
   }

   .accessory-tabs-header h6 {
      margin: 0;
      color: #495057;
      font-weight: 600;
      font-size: 0.9rem;
   }

   .accessory-tabs-container {
      flex: 1;
      overflow-y: auto;
      padding: 8px;
   }

   .accessory-tab {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 8px 12px;
      border-radius: 4px;
      cursor: pointer;
      transition: all 0.2s ease;
      margin-bottom: 4px;
      border: 1px solid transparent;
   }

   .accessory-tab:hover {
      background: #e9ecef;
   }

   .accessory-tab.active {
      background: white;
      border-color: #4361ee;
      box-shadow: 0 1px 3px rgba(67, 97, 238, 0.2);
   }

   .accessory-tab-name {
      font-weight: 500;
      color: #495057;
      font-size: 0.8rem;
      flex: 1;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
   }

   .accessory-tab-close {
      width: 16px;
      height: 16px;
      border-radius: 3px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #6c757d;
      transition: all 0.2s ease;
      font-size: 0.65rem;
      opacity: 0;
   }

   .accessory-tab:hover .accessory-tab-close {
      opacity: 1;
   }

   .accessory-tab-close:hover {
      background: rgba(220, 53, 69, 0.1);
      color: #dc3545;
   }

   .accessory-details-content {
      background: white;
      border-radius: 6px;
      border: 1px solid #e0e0e0;
      height: 100%;
      display: flex;
      flex-direction: column;
   }

   .accessory-details-header {
      padding: 8px 16px;
      border-bottom: 1px solid #e0e0e0;
      background: #f8f9fa;
      border-radius: 6px 6px 0 0;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 8px;
   }

   .accessory-details-header h6 {
      margin: 0;
      color: #495057;
      font-weight: 600;
      font-size: 0.9rem;
      white-space: nowrap;
   }

   .accessory-details-body {
      flex: 1;
      padding: 16px;
      overflow-y: auto;
      display: flex;
      flex-direction: column;
   }

   .empty-accessory-selection {
      text-align: center;
      padding: 40px 20px;
      color: #6c757d;
   }

   .empty-accessory-selection i {
      font-size: 3rem;
      margin-bottom: 12px;
      color: #adb5bd;
      opacity: 0.6;
   }

   .empty-accessory-selection p {
      margin: 0 0 16px 0;
      font-size: 0.9rem;
   }

   .empty-accessory-tabs {
      text-align: center;
      padding: 40px 20px;
      color: #6c757d;
   }

   .empty-accessory-tabs i {
      font-size: 2.5rem;
      margin-bottom: 12px;
      color: #adb5bd;
      opacity: 0.6;
   }

   .empty-accessory-tabs p {
      margin: 0 0 16px 0;
      font-size: 0.85rem;
   }

   .accessory-option {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 12px;
   }

   .accessory-option-image {
      width: 80px;
      height: 80px;
      border-radius: 6px;
      border: 1px solid #e0e0e0;
      background: white;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
   }

   .accessory-option-image img {
      width: 100%;
      height: 100%;
      object-fit: cover;
   }

   .accessory-option-image i {
      color: #6c757d;
      font-size: 1.5rem;
   }

   .accessory-option-details {
      flex: 1;
   }

   .accessory-option-name {
      font-weight: 600;
      color: #495057;
      font-size: 0.85rem;
      margin-bottom: 4px;
   }

   .accessory-option-description {
      color: #6c757d;
      font-size: 0.75rem;
      line-height: 1.3;
   }

   @media (max-width: 1200px) {
      .qualification-options {
         grid-template-columns: 1fr;
      }

      .multi-select-options {
         grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
      }

      .item-categories {
         grid-template-columns: 1fr;
         height: auto;
      }

      .item-categories-sidebar {
         order: 2;
      }

      .accessory-layout {
         grid-template-columns: 1fr;
         gap: 12px;
      }

      .accessory-tabs-sidebar {
         min-height: 200px;
      }
   }

   @media (max-width: 768px) {
      .product-tabs-header {
         flex-direction: column;
         gap: 12px;
         align-items: stretch;
      }

      .room-info-form {
         flex-direction: column;
         gap: 8px;
      }

      .form-group-small {
         min-width: auto;
      }

      .product-tabs-container {
         padding: 8px;
      }

      .qualification-modal-content {
         width: 95%;
         margin: 8px;
      }

      .nav-tabs {
         flex-wrap: wrap;
      }

      .nav-tabs .nav-link {
         margin-bottom: 4px;
      }

      .multi-select-options {
         grid-template-columns: 1fr;
      }

      .dimensions-inputs,
      .price-inputs {
         grid-template-columns: 1fr;
      }

      .enhanced-item-details {
         grid-template-columns: 1fr;
      }

      .product-header-grid {
         grid-template-columns: 1fr;
      }

      .compact-details-grid {
         grid-template-columns: 1fr;
         gap: 12px;
      }

      .complex-product-layout {
         grid-template-columns: 1fr;
         gap: 12px;
      }

      .items-tabs-sidebar {
         min-height: 200px;
      }

      .item-details-content {
         min-height: 400px;
      }

      .item-details-header {
         flex-direction: column;
         align-items: flex-start;
         gap: 8px;
      }

      .compact-header-details {
         width: 100%;
         justify-content: space-between;
      }

      .compact-header-group input {
         width: 60px;
      }

      .compact-details-with-image {
         grid-template-columns: 1fr;
      }

      .enhanced-details-with-image {
         grid-template-columns: 1fr;
      }

      .material-inputs-compact {
         grid-template-columns: 1fr;
      }

      .material-compact-fields {
         grid-template-columns: 1fr;
      }

      .pillow-subcategories-tabs {
         flex-direction: column;
      }

      .curtain-controls {
         grid-template-columns: 1fr;
      }
   }

   .items-tabs-container::-webkit-scrollbar {
      width: 4px;
   }

   .items-tabs-container::-webkit-scrollbar-track {
      background: #f1f1f1;
      border-radius: 2px;
   }

   .items-tabs-container::-webkit-scrollbar-thumb {
      background: #c1c1c1;
      border-radius: 2px;
   }

   .item-details-body::-webkit-scrollbar {
      width: 4px;
   }

   .item-details-body::-webkit-scrollbar-track {
      background: #f1f1f1;
      border-radius: 2px;
   }

   .item-details-body::-webkit-scrollbar-thumb {
      background: #c1c1c1;
      border-radius: 2px;
   }

   .qualification-modal-body::-webkit-scrollbar {
      width: 4px;
   }

   .qualification-modal-body::-webkit-scrollbar-track {
      background: #f1f1f1;
      border-radius: 2px;
   }

   .multi-select-modal-body::-webkit-scrollbar {
      width: 4px;
   }

   .multi-select-modal-body::-webkit-scrollbar-track {
      background: #f1f1f1;
      border-radius: 2px;
   }

   .item-selection-modal-body::-webkit-scrollbar {
      width: 4px;
   }

   .item-selection-modal-body::-webkit-scrollbar-track {
      background: #f1f1f1;
      border-radius: 2px;
   }

   .accessory-tabs-container::-webkit-scrollbar {
      width: 4px;
   }

   .accessory-tabs-container::-webkit-scrollbar-track {
      background: #f1f1f1;
      border-radius: 2px;
   }

   .accessory-tabs-container::-webkit-scrollbar-thumb {
      background: #c1c1c1;
      border-radius: 2px;
   }

   .accessory-details-body::-webkit-scrollbar {
      width: 4px;
   }

   .accessory-details-body::-webkit-scrollbar-track {
      background: #f1f1f1;
      border-radius: 2px;
   }

   .accessory-details-body::-webkit-scrollbar-thumb {
      background: #c1c1c1;
      border-radius: 2px;
   }
</style>

<div class="order-product-wrapper">
   <ul class="nav nav-tabs" id="roomTabs" role="tablist">
      <li class="nav-item">
         <a class="nav-link active room-tab" id="room1-tab" data-toggle="tab" href="#room1" role="tab"
            aria-controls="room1" aria-selected="true" data-room="1">
            <div class="room-header">
               <span class="status-indicator status-empty"></span>
               <span class="room-title">Room 1</span>
               <span class="close-room ml-2" title="Remove room">
                  <i class="fa fa-times"></i>
               </span>
            </div>
         </a>
      </li>
      <li class="nav-item ml-auto pl-2">
         <button type="button" class="btn btn-sm btn-primary add-room-btn" id="addRoomBtn">
            <i class="fa fa-plus mr-1"></i> Add Room
         </button>
      </li>
   </ul>

   <div class="tab-content" id="roomTabsContent">
      <div class="tab-pane fade show active" id="room1" role="tabpanel" aria-labelledby="room1-tab" data-room="1">
         <div class="product-tabs-wrapper">
            <div class="product-tabs-header">
               <div class="room-info-form">
                  <div class="form-group-small">
                     <label for="floorName-room1">Floor Name</label>
                     <input type="text" class="form-control-small" id="floorName-room1" placeholder="Enter floor name">
                  </div>
                  <div class="form-group-small">
                     <label for="roomName-room1">Room Name</label>
                     <input type="text" class="form-control-small" id="roomName-room1" placeholder="Enter room name">
                  </div>
                  <div class="form-group-small">
                     <label>Room Image</label>
                     <div class="image-upload-container">
                        <div class="image-preview" id="imagePreview-room1">
                           <i class="fa fa-image"></i>
                        </div>
                        <div class="file-input-wrapper">
                           <button type="button" class="btn btn-sm btn-outline-primary">
                              <i class="fa fa-upload mr-1"></i> Upload
                           </button>
                           <input type="file" class="room-image-input" id="roomImage-room1" data-file-type="image" data-room="1">
                        </div>
                     </div>
                  </div>
               </div>
               <button type="button" class="btn btn-sm add-item-to-room-btn" data-room="1">
                  <i class="fa fa-plus mr-1"></i> Add Item To Room 1
               </button>
            </div>
            <div class="product-tabs-container" id="productTabs-room1">
               <div class="product-empty-state">
                  <i class="fa fa-cube"></i>
                  <p>No products added yet</p>
               </div>
            </div>
            <div class="product-content-area" id="productContent-room1">
               <div class="product-empty-state">
                  <i class="fa fa-hand-pointer"></i>
                  <p>Select a product to configure details</p>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>

<!-- Qualification Modal -->
<div class="qualification-modal" id="qualificationModal">
   <div class="qualification-modal-content">
      <div class="qualification-modal-header">
         <h5><i class="fa fa-plus-circle mr-2"></i>Select Product Type</h5>
      </div>
      <div class="search-container">
         <input type="text" class="search-input" id="qualificationSearch" placeholder="Search product types...">
      </div>
      <div class="qualification-modal-body">
         <div class="qualification-options" id="qualificationOptions"></div>
      </div>
      <div class="qualification-modal-footer">
         <button type="button" class="btn btn-secondary" id="closeQualificationModal">Cancel</button>
         <button type="button" class="btn btn-primary" id="confirmAddQualification" disabled>Next</button>
      </div>
   </div>
</div>

<!-- Multi-Select Products Modal -->
<div class="multi-select-modal" id="multiSelectModal">
   <div class="multi-select-modal-content">
      <div class="multi-select-modal-header">
         <h5><i class="fa fa-layer-group mr-2"></i>Select Products</h5>
      </div>
      <div class="search-container">
         <input type="text" class="search-input" id="productSearch" placeholder="Search products...">
      </div>
      <div class="multi-select-modal-body">
         <div class="multi-select-options" id="multiSelectOptions"></div>
      </div>
      <div class="multi-select-modal-footer">
         <button type="button" class="btn btn-secondary" id="closeMultiSelectModal">Cancel</button>
         <button type="button" class="btn btn-primary" id="confirmMultiSelect" disabled>Add Selected Products</button>
      </div>
   </div>
</div>

<!-- Item Selection Modal -->
<div class="item-selection-modal" id="itemSelectionModal">
   <div class="item-selection-modal-content">
      <div class="item-selection-modal-header">
         <h5><i class="fa fa-cube mr-2"></i>Select Item</h5>
      </div>
      <div class="item-selection-modal-body">
         <div class="item-categories">
            <div class="item-categories-sidebar">
               <div class="item-category-tabs" id="itemCategoryTabs"></div>
            </div>
            <div class="item-category-content">
               <div class="item-options" id="itemOptions"></div>
            </div>
         </div>
      </div>
      <div class="item-selection-modal-footer">
         <button type="button" class="btn btn-secondary" id="closeItemSelectionModal">Cancel</button>
         <button type="button" class="btn btn-primary" id="confirmSelectItem" disabled>Add Item</button>
      </div>
   </div>
</div>

<!-- Accessory Selection Modal -->
<div class="item-selection-modal" id="accessorySelectionModal">
   <div class="item-selection-modal-content">
      <div class="item-selection-modal-header">
         <h5><i class="fa fa-plus-circle mr-2"></i>Select Accessory</h5>
      </div>
      <div class="item-selection-modal-body">
         <div class="item-options" id="accessoryOptions"></div>
      </div>
      <div class="item-selection-modal-footer">
         <button type="button" class="btn btn-secondary" id="closeAccessorySelectionModal">Cancel</button>
         <button type="button" class="btn btn-primary" id="confirmSelectAccessory" disabled>Add Accessory</button>
      </div>
   </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
jQuery(function($) {
   // State management
   const state = {
      rooms: [],
      currentRoom: null,
      selectedQualification: null,
      selectedProducts: [],
      selectedMaterialCategory: null,
      selectedPillowSubcategory: null,
      currentProductType: null,
      selectedItem: null,
      selectedAccessory: null,
      currentProductId: null
   };

   // Updated material categories data with pillow subcategories
   const materialCategories = [
      {
         id: 'metal',
         name: 'Metal',
         description: 'Steel, aluminum, brass, and other metal materials',
         icon: 'fa-hammer',
         color: 'linear-gradient(135deg, #6c757d, #495057)',
         defaultMaterials: [
            { id: 'steel', name: 'Steel', description: 'Carbon steel material' },
            { id: 'aluminum', name: 'Aluminum', description: 'Lightweight aluminum' },
            { id: 'brass', name: 'Brass', description: 'Brass alloy material' },
            { id: 'copper', name: 'Copper', description: 'Copper material' }
         ]
      },
      {
         id: 'wood-wallpaper',
         name: 'Wood & Wallpaper',
         description: 'Wood materials and wall covering papers',
         icon: 'fa-tree',
         color: 'linear-gradient(135deg, #8b4513, #a0522d)',
         defaultMaterials: [
            { id: 'oak', name: 'Oak Wood', description: 'Solid oak wood' },
            { id: 'pine', name: 'Pine Wood', description: 'Pine wood material' },
            { id: 'vinyl-wallpaper', name: 'Vinyl Wallpaper', description: 'Vinyl wall covering' },
            { id: 'fabric-wallpaper', name: 'Fabric Wallpaper', description: 'Fabric-based wallpaper' }
         ]
      },
      {
         id: 'marble',
         name: 'Marble',
         description: 'Natural and engineered marble stones',
         icon: 'fa-gem',
         color: 'linear-gradient(135deg, #c0c0c0, #a9a9a9)',
         defaultMaterials: [
            { id: 'carrara', name: 'Carrara Marble', description: 'White Carrara marble' },
            { id: 'calacatta', name: 'Calacatta Marble', description: 'Luxury Calacatta marble' },
            { id: 'engineered-marble', name: 'Engineered Marble', description: 'Composite marble' },
            { id: 'travertine', name: 'Travertine', description: 'Natural travertine stone' }
         ]
      },
      {
         id: 'glass',
         name: 'Glass',
         description: 'Various types of glass materials',
         icon: 'fa-wine-glass',
         color: 'linear-gradient(135deg, #4ecdc4, #44a08d)',
         defaultMaterials: [
            { id: 'clear-glass', name: 'Clear Glass', description: 'Transparent glass' },
            { id: 'tinted-glass', name: 'Tinted Glass', description: 'Colored glass' },
            { id: 'tempered-glass', name: 'Tempered Glass', description: 'Safety tempered glass' },
            { id: 'frosted-glass', name: 'Frosted Glass', description: 'Frosted finish glass' }
         ]
      },
      {
         id: 'fabric-rope',
         name: 'Fabric & Rope',
         description: 'Textiles, fabrics, and rope materials',
         icon: 'fa-scroll',
         color: 'linear-gradient(135deg, #ff6b6b, #ee5a52)',
         defaultMaterials: [
            { id: 'cotton', name: 'Cotton Fabric', description: 'Natural cotton material' },
            { id: 'polyester', name: 'Polyester Fabric', description: 'Synthetic polyester' },
            { id: 'nylon-rope', name: 'Nylon Rope', description: 'Strong nylon rope' },
            { id: 'hemp-rope', name: 'Hemp Rope', description: 'Natural hemp rope' }
         ]
      },
      {
         id: 'pillow',
         name: 'Pillow',
         description: 'Pillow materials and components',
         icon: 'fa-couch',
         color: 'linear-gradient(135deg, #a8e6cf, #56ab2f)',
         subcategories: [
            { id: 'default-pillow', name: 'Default Pillow', description: 'Standard pillow material', icon: 'fa-cube' },
            { id: 'pillow-face', name: 'Pillow Face', description: 'Pillow front surface material', icon: 'fa-square' },
            { id: 'pillow-back', name: 'Pillow Back', description: 'Pillow back surface material', icon: 'fa-square' },
            { id: 'piping', name: 'Piping', description: 'Pillow piping and edges', icon: 'fa-grip-lines' }
         ],
         defaultMaterials: [
            { id: 'memory-foam', name: 'Memory Foam', description: 'Comfort memory foam' },
            { id: 'poly-fill', name: 'Polyester Fill', description: 'Synthetic pillow filling' },
            { id: 'feathers', name: 'Feathers', description: 'Natural feather filling' },
            { id: 'cotton-cover', name: 'Cotton Cover', description: 'Cotton pillow cover' }
         ]
      }
   ];

   // Product data - ADDED CURTAINS QUALIFICATION
   const productOptions = [
      {
         id: 'fitout',
         name: 'Fitout',
         description: 'Interior construction, walls, ceilings, and flooring',
         icon: 'fa-paint-roller',
         color: 'linear-gradient(135deg, #4361ee, #3a0ca3)',
         type: 'complex'
      },
      {
         id: 'curtains',
         name: 'Curtains',
         description: 'Window treatments, blinds, and curtain systems',
         icon: 'fa-window-restore',
         color: 'linear-gradient(135deg, #7209b7, #3a0ca3)',
         type: 'curtains'
      },
      {
         id: 'electrical',
         name: 'Electrical Works',
         description: 'Wiring, lighting, switches, and electrical systems',
         icon: 'fa-bolt',
         color: 'linear-gradient(135deg, #ff9a00, #ff6b6b)',
         type: 'simple'
      },
      {
         id: 'plumbing',
         name: 'Plumbing Systems',
         description: 'Pipes, fixtures, drainage, and water systems',
         icon: 'fa-faucet',
         color: 'linear-gradient(135deg, #4ecdc4, #44a08d)',
         type: 'simple'
      },
      {
         id: 'painting',
         name: 'Painting & Finishing',
         description: 'Paints, coatings, and surface finishing materials',
         icon: 'fa-brush',
         color: 'linear-gradient(135deg, #a8e6cf, #56ab2f)',
         type: 'simple'
      }
   ];

   // Fitout products (Wall, Ceiling, Ground)
   const fitoutProducts = [
      {
         id: 'wall',
         name: 'Wall',
         description: 'Wall construction and finishing',
         icon: 'fa-wall',
         color: 'linear-gradient(135deg, #ff6b6b, #ee5a52)',
         type: 'complex'
      },
      {
         id: 'ceiling',
         name: 'Ceiling',
         description: 'Ceiling systems and fixtures',
         icon: 'fa-border-all',
         color: 'linear-gradient(135deg, #4ecdc4, #44a08d)',
         type: 'complex'
      },
      {
         id: 'ground',
         name: 'Ground',
         description: 'Flooring and ground works',
         icon: 'fa-square',
         color: 'linear-gradient(135deg, #45b7d1, #4a7bd6)',
         type: 'complex'
      }
   ];

   // ADDED: Curtain products
   const curtainProducts = [
      {
         id: 'blinds',
         name: 'Blinds',
         description: 'Window blinds and shades',
         icon: 'fa-grip-lines',
         color: 'linear-gradient(135deg, #7209b7, #3a0ca3)',
         type: 'curtains'
      },
      {
         id: 'chiffon',
         name: 'Chiffon',
         description: 'Sheer chiffon curtains',
         icon: 'fa-scroll',
         color: 'linear-gradient(135deg, #f72585, #b5179e)',
         type: 'curtains'
      },
      {
         id: 'main-curtains',
         name: 'Main Curtains',
         description: 'Primary curtain panels',
         icon: 'fa-window-restore',
         color: 'linear-gradient(135deg, #4361ee, #3a0ca3)',
         type: 'curtains'
      },
      {
         id: 'main-curtains-blinds',
         name: 'Main Curtains with Blinds',
         description: 'Curtains with integrated blinds',
         icon: 'fa-layer-group',
         color: 'linear-gradient(135deg, #4cc9f0, #4895ef)',
         type: 'curtains'
      },
      {
         id: 'main-curtains-chiffon',
         name: 'Main Curtains with Chiffon',
         description: 'Curtains with chiffon overlay',
         icon: 'fa-layer-group',
         color: 'linear-gradient(135deg, #f72585, #7209b7)',
         type: 'curtains'
      },
      {
         id: 'main-curtains-blind-chiffon',
         name: 'Main Curtains With Blind and Chiffon',
         description: 'Complete window treatment system',
         icon: 'fa-layer-group',
         color: 'linear-gradient(135deg, #3a0ca3, #4361ee)',
         type: 'curtains'
      }
   ];

   // Item data for complex products
   const itemData = {
      'wall': {
         name: 'Wall Items',
         categories: {
            'construction': {
               name: 'Construction',
               items: [
                  { id: 'drywall', name: 'Drywall', description: 'Standard drywall panels', icon: 'fa-layer-group', color: '#ff6b6b' },
                  { id: 'studs', name: 'Wall Studs', description: 'Metal or wood studs', icon: 'fa-grip-lines', color: '#ee5a52' }
               ]
            },
            'finishing': {
               name: 'Finishing',
               items: [
                  { id: 'paint', name: 'Wall Paint', description: 'Interior wall paint', icon: 'fa-paint-roller', color: '#4361ee' },
                  { id: 'wallpaper', name: 'Wallpaper', description: 'Wall covering material', icon: 'fa-scroll', color: '#3a0ca3' }
               ]
            }
         }
      },
      'ceiling': {
         name: 'Ceiling Items',
         categories: {
            'materials': {
               name: 'Materials',
               items: [
                  { id: 'ceiling_tiles', name: 'Ceiling Tiles', description: 'Acoustic ceiling tiles', icon: 'fa-border-all', color: '#4ecdc4' },
                  { id: 'gypsum', name: 'Gypsum Board', description: 'Ceiling gypsum boards', icon: 'fa-square', color: '#44a08d' }
               ]
            }
         }
      },
      'ground': {
         name: 'Ground Items',
         categories: {
            'flooring': {
               name: 'Flooring',
               items: [
                  { id: 'tiles', name: 'Floor Tiles', description: 'Ceramic or porcelain tiles', icon: 'fa-th-large', color: '#45b7d1' },
                  { id: 'hardwood', name: 'Hardwood', description: 'Hardwood flooring', icon: 'fa-tree', color: '#8b4513' }
               ]
            }
         }
      },
      'electrical': {
         name: 'Electrical Items',
         categories: {
            'fixtures': {
               name: 'Fixtures',
               items: [
                  { id: 'switches', name: 'Switches', description: 'Electrical switches', icon: 'fa-toggle-on', color: '#ff9a00' },
                  { id: 'outlets', name: 'Outlets', description: 'Power outlets', icon: 'fa-plug', color: '#ff6b6b' },
                  { id: 'lighting', name: 'Lighting', description: 'Light fixtures', icon: 'fa-lightbulb', color: '#ffd166' }
               ]
            }
         }
      },
      'plumbing': {
         name: 'Plumbing Items',
         categories: {
            'fixtures': {
               name: 'Fixtures',
               items: [
                  { id: 'faucet', name: 'Faucet', description: 'Water faucet', icon: 'fa-faucet', color: '#4ecdc4' },
                  { id: 'pipe', name: 'Pipe', description: 'Water pipe', icon: 'fa-pipe', color: '#45b7d1' },
                  { id: 'valve', name: 'Valve', description: 'Water valve', icon: 'fa-toggle-off', color: '#4361ee' }
               ]
            },
            'drainage': {
               name: 'Drainage',
               items: [
                  { id: 'drain', name: 'Drain', description: 'Drain pipe', icon: 'fa-water', color: '#3a0ca3' },
                  { id: 'trap', name: 'Trap', description: 'Pipe trap', icon: 'fa-undo', color: '#7209b7' }
               ]
            }
         }
      },
      'painting': {
         name: 'Painting Items',
         categories: {
            'materials': {
               name: 'Materials',
               items: [
                  { id: 'paint_bucket', name: 'Paint Bucket', description: 'Paint container', icon: 'fa-fill-drip', color: '#a8e6cf' },
                  { id: 'brush', name: 'Brush', description: 'Paint brush', icon: 'fa-paint-brush', color: '#56ab2f' },
                  { id: 'roller', name: 'Roller', description: 'Paint roller', icon: 'fa-paint-roller', color: '#a8e6cf' }
               ]
            }
         }
      }
   };

   // ADDED: Curtain accessory options
   const curtainAccessories = [
      {
         id: 'side-holder',
         name: 'Side Holder',
         description: 'Curtain side holders and accessories',
         icon: 'fa-grip-lines-vertical',
         color: 'linear-gradient(135deg, #7209b7, #3a0ca3)',
         options: [
            { id: 'holder1', name: 'Classic Side Holder', description: 'Traditional side holder design', image: 'holder1.jpg' },
            { id: 'holder2', name: 'Modern Side Holder', description: 'Contemporary side holder design', image: 'holder2.jpg' },
            { id: 'holder3', name: 'Decorative Side Holder', description: 'Ornamental side holder design', image: 'holder3.jpg' }
         ]
      },
      {
         id: 'black-out',
         name: 'Black Out',
         description: 'Black out lining and accessories',
         icon: 'fa-moon',
         color: 'linear-gradient(135deg, #2b2d42, #1d1e2c)',
         options: [
            { id: 'blackout1', name: 'Standard Black Out', description: 'Basic black out lining', image: 'blackout1.jpg' },
            { id: 'blackout2', name: 'Thermal Black Out', description: 'Insulated black out lining', image: 'blackout2.jpg' },
            { id: 'blackout3', name: 'Premium Black Out', description: 'Luxury black out lining', image: 'blackout3.jpg' }
         ]
      }
   ];

   // Initialize qualification modal
   function initializeQualificationModal() {
      const $optionsContainer = $('#qualificationOptions');
      $optionsContainer.empty();

      productOptions.forEach(qual => {
         const $option = $(`
            <div class="qualification-option" data-qualification="${qual.id}">
               <div class="qualification-option-header">
                  <div class="qualification-option-icon" style="background: ${qual.color};">
                     <i class="fa ${qual.icon}"></i>
                  </div>
                  <div class="qualification-option-name">${qual.name}</div>
               </div>
               <div class="qualification-option-description">${qual.description}</div>
            </div>
         `);
         $optionsContainer.append($option);
      });
   }

   // Initialize multi-select modal
   function initializeMultiSelectModal(qualification) {
      const $optionsContainer = $('#multiSelectOptions');
      $optionsContainer.empty();
      state.selectedProducts = [];

      let productsToShow = [];

      if (qualification.id === 'fitout') {
         productsToShow = fitoutProducts;
      } else if (qualification.id === 'curtains') {
         productsToShow = curtainProducts;
      } else {
         productsToShow = [qualification];
      }

      productsToShow.forEach(product => {
         const $option = $(`
            <div class="multi-select-option" data-product-id="${product.id}">
               <div class="multi-select-option-header">
                  <div class="multi-select-option-icon" style="background: ${product.color};">
                     <i class="fa ${product.icon}"></i>
                  </div>
                  <div class="multi-select-option-name">${product.name}</div>
               </div>
               <div class="multi-select-option-description">${product.description}</div>
            </div>
         `);
         $optionsContainer.append($option);
      });
   }

   // Search functionality for qualification modal
   function setupQualificationSearch() {
      $('#qualificationSearch').on('input', function() {
         const searchTerm = $(this).val().toLowerCase();
         $('.qualification-option').each(function() {
            const $option = $(this);
            const name = $option.find('.qualification-option-name').text().toLowerCase();
            const description = $option.find('.qualification-option-description').text().toLowerCase();

            if (name.includes(searchTerm) || description.includes(searchTerm)) {
               $option.show();
            } else {
               $option.hide();
            }
         });
      });
   }

   // Search functionality for product modal
   function setupProductSearch() {
      $('#productSearch').on('input', function() {
         const searchTerm = $(this).val().toLowerCase();
         $('.multi-select-option').each(function() {
            const $option = $(this);
            const name = $option.find('.multi-select-option-name').text().toLowerCase();
            const description = $option.find('.multi-select-option-description').text().toLowerCase();

            if (name.includes(searchTerm) || description.includes(searchTerm)) {
               $option.show();
            } else {
               $option.hide();
            }
         });
      });
   }

   // Updated image upload functionality with larger image sizes
   function setupImageUpload() {
      $(document).on('change', '.room-image-input', function() {
         const file = this.files[0];
         const roomId = $(this).data('room');
         const $preview = $(`#imagePreview-room${roomId}`);

         if (file) {
            const reader = new FileReader();

            reader.onload = function(e) {
               $preview.html(`<img src="${e.target.result}" alt="Room image">`);
            }

            reader.readAsDataURL(file);
         }
      });

      // Material image upload - updated for larger images (160px)
      $(document).on('change', '.material-image-input', function() {
         const file = this.files[0];
         const $preview = $(this).closest('.material-inputs-compact').find('.material-compact-image');

         if (file) {
            const reader = new FileReader();

            reader.onload = function(e) {
               $preview.html(`<img src="${e.target.result}" alt="Material image">`);
            }

            reader.readAsDataURL(file);
         }
      });

      // Pillow image upload
      $(document).on('change', '.pillow-image-input', function() {
         const file = this.files[0];
         const $preview = $(this).closest('.material-inputs-compact').find('.material-compact-image');

         if (file) {
            const reader = new FileReader();

            reader.onload = function(e) {
               $preview.html(`<img src="${e.target.result}" alt="Pillow image">`);
            }

            reader.readAsDataURL(file);
         }
      });

      // Header image upload
      $(document).on('change', '.header-image-input', function() {
         const file = this.files[0];
         const $preview = $(this).closest('.product-header-with-image').find('.header-image-preview');

         if (file) {
            const reader = new FileReader();

            reader.onload = function(e) {
               $preview.html(`<img src="${e.target.result}" alt="Header image">`);
            }

            reader.readAsDataURL(file);
         }
      });
   }

   // Get next room number
   function getNextRoomNumber() {
      if (state.rooms.length === 0) return 1;
      return Math.max(...state.rooms) + 1;
   }

   // Add room to state
   function addRoomToState(roomNumber) {
      state.rooms.push(roomNumber);
      state.rooms.sort((a, b) => a - b);
   }

   // Remove room from state
   function removeRoomFromState(roomNumber) {
      state.rooms = state.rooms.filter(num => num !== roomNumber);
   }

   // Renumber all rooms
   function renumberRooms() {
      const $roomTabs = $('#roomTabs .room-tab').get();

      $roomTabs.forEach((tab, index) => {
         const roomNumber = index + 1;
         const $tab = $(tab);
         const oldRoomId = $tab.attr('id').replace('-tab', '');
         const newRoomId = `room${roomNumber}`;

         // Update tab
         $tab.attr('id', `${newRoomId}-tab`);
         $tab.attr('href', `#${newRoomId}`);
         $tab.attr('aria-controls', newRoomId);
         $tab.data('room', roomNumber);
         $tab.find('.room-title').text(`Room ${roomNumber}`);

         // Update pane
         const $pane = $(`#${oldRoomId}`);
         $pane.attr('id', newRoomId);
         $pane.attr('aria-labelledby', `${newRoomId}-tab`);
         $pane.data('room', roomNumber);

         // Update product containers
         $pane.find('.product-tabs-container').attr('id', `productTabs-room${roomNumber}`);
         $pane.find('.product-content-area').attr('id', `productContent-room${roomNumber}`);

         // Update form fields
         $pane.find('#floorName-' + oldRoomId).attr('id', 'floorName-' + newRoomId);
         $pane.find('#roomName-' + oldRoomId).attr('id', 'roomName-' + newRoomId);
         $pane.find('#roomImage-' + oldRoomId).attr('id', 'roomImage-' + newRoomId).data('room', roomNumber);
         $pane.find('#imagePreview-' + oldRoomId).attr('id', 'imagePreview-' + newRoomId);

         // Update buttons
         $pane.find('.add-item-to-room-btn').data('room', roomNumber);

         // Update product tabs
         $pane.find('.product-tab').each(function() {
            const $productTab = $(this);
            const productId = $productTab.data('product');
            const newTabId = `product-${productId}-room${roomNumber}`;

            $productTab.attr('id', `${newTabId}-tab`);

            const $productContent = $(`#product-${productId}-${oldRoomId}`);
            if ($productContent.length) {
               $productContent.attr('id', newTabId);
            }
         });
      });

      // Update state
      state.rooms = $roomTabs.map((tab, index) => index + 1);
   }

   // Modal management functions
   function showQualificationModal(roomId) {
      console.log('Opening qualification modal for room:', roomId);
      state.currentRoom = roomId;
      state.selectedQualification = null;
      $('#qualificationModal').fadeIn(300);
      $('#qualificationOptions .qualification-option').removeClass('selected');
      $('#confirmAddQualification').prop('disabled', true);
      $('#qualificationSearch').val('');
      $('.qualification-option').show();
   }

   function hideQualificationModal() {
      $('#qualificationModal').fadeOut(300);
      state.selectedQualification = null;
   }

   function showMultiSelectModal(qualification, roomId) {
      console.log('Opening multi-select modal for:', qualification.name, 'room:', roomId);

      $('#multiSelectModal')
         .data('qualification', qualification)
         .data('roomId', roomId);

      state.currentRoom = roomId;

      initializeMultiSelectModal(qualification);

      $('#multiSelectModal').fadeIn(300);
      $('#multiSelectOptions .multi-select-option').removeClass('selected');
      $('#confirmMultiSelect').prop('disabled', true);
      $('#productSearch').val('');
      $('.multi-select-option').show();
   }

   function hideMultiSelectModal() {
      $('#multiSelectModal').fadeOut(300);
      state.selectedProducts = [];
      $('#multiSelectModal').removeData('qualification');
      $('#multiSelectModal').removeData('roomId');
   }

   function showItemSelectionModal(productType) {
      console.log('Opening item selection modal for product:', productType);
      state.currentProductType = productType;

      const $modal = $('#itemSelectionModal');
      const $categoryTabs = $('#itemCategoryTabs');
      const $itemOptions = $('#itemOptions');

      $categoryTabs.empty();
      $itemOptions.empty();

      const productData = itemData[productType] || itemData.electrical;
      const categories = productData.categories;

      let firstCategory = null;
      const categoryKeys = Object.keys(categories);

      if (categoryKeys.length > 1) {
         categoryKeys.forEach((catKey, index) => {
            const categoryInfo = categories[catKey];
            if (index === 0) firstCategory = catKey;

            const $tab = $(`
                <div class="item-category-tab ${index === 0 ? 'active' : ''}" data-category="${catKey}">
                   ${categoryInfo.name}
                </div>
            `);
            $categoryTabs.append($tab);
         });
      } else {
         $categoryTabs.hide();
         firstCategory = categoryKeys[0];
      }

      if (firstCategory) {
         loadItemCategory(firstCategory, categories[firstCategory]);
      }

      $modal.fadeIn(300);
      $('#confirmSelectItem').prop('disabled', true);
   }

   function loadItemCategory(categoryKey, categoryInfo) {
      const $itemOptions = $('#itemOptions');
      $itemOptions.empty();

      categoryInfo.items.forEach(item => {
         const $option = $(`
            <div class="item-option" data-item-id="${item.id}">
               <div class="item-option-icon" style="background: ${item.color};">
                  <i class="fa ${item.icon}"></i>
               </div>
               <div class="item-option-name">${item.name}</div>
               <div class="item-option-description">${item.description}</div>
            </div>
         `);
         $itemOptions.append($option);
      });
   }

   function hideItemSelectionModal() {
      $('#itemSelectionModal').fadeOut(300);
      state.currentProductType = null;
      state.selectedItem = null;
   }

   // ADDED: Show accessory selection modal
   function showAccessorySelectionModal(productId) {
      console.log('Opening accessory selection modal for product:', productId);
      state.currentProductId = productId;

      const $modal = $('#accessorySelectionModal');
      const $accessoryOptions = $('#accessoryOptions');

      $accessoryOptions.empty();

      curtainAccessories.forEach(accessory => {
         const $option = $(`
            <div class="item-option" data-accessory-id="${accessory.id}">
               <div class="item-option-icon" style="background: ${accessory.color};">
                  <i class="fa ${accessory.icon}"></i>
               </div>
               <div class="item-option-name">${accessory.name}</div>
               <div class="item-option-description">${accessory.description}</div>
            </div>
         `);
         $accessoryOptions.append($option);
      });

      $modal.fadeIn(300);
      $('#confirmSelectAccessory').prop('disabled', true);
   }

   function hideAccessorySelectionModal() {
      $('#accessorySelectionModal').fadeOut(300);
      state.currentProductId = null;
      state.selectedAccessory = null;
   }

   // Add product tab
   function addProductTab(roomId, product) {
      console.log('Adding product:', product.name, 'to room:', roomId);

      const $tabsContainer = $(`#productTabs-room${roomId}`);
      const $emptyState = $tabsContainer.find('.product-empty-state');

      if ($emptyState.length) {
         $emptyState.remove();
      }

      const productId = `product-${product.id}-room${roomId}`;
      const tabId = `${productId}-tab`;

      if ($tabsContainer.find(`[data-product="${product.id}"]`).length) {
         alert('This product has already been added to this room.');
         return;
      }

      const $tab = $(`
        <div class="product-tab" data-product="${product.id}" id="${tabId}">
           <div class="product-tab-icon" style="background: ${product.color};">
              <i class="fa ${product.icon}"></i>
           </div>
           <span class="product-tab-name">${product.name}</span>
           <div class="product-tab-close" title="Remove product">
              <i class="fa fa-times"></i>
           </div>
        </div>
     `);

      $tabsContainer.append($tab);

      const $contentArea = $(`#productContent-room${roomId}`);
      const $emptyContent = $contentArea.find('.product-empty-state');

      if ($emptyContent.length) {
         $emptyContent.remove();
      }

      const $content = $(`
        <div class="product-content" id="${productId}" style="display: none;">
           <div class="loading-state">
              <i class="fa fa-spinner fa-spin fa-2x"></i>
              <p>Loading ${product.name} details...</p>
           </div>
        </div>
     `);

      $contentArea.append($content);

      activateProductTab($tab);

      setTimeout(() => {
         loadProductContent(productId, product);
      }, 500);
   }

   function activateProductTab($tab) {
      const productId = $tab.data('product');
      const roomId = $tab.closest('.product-tabs-container').attr('id').replace('productTabs-room', '');

      $(`#productTabs-room${roomId} .product-tab`).removeClass('active');
      $(`#productContent-room${roomId} .product-content`).hide();

      $tab.addClass('active');
      $(`#product-${productId}-room${roomId}`).show();
   }

   function loadProductContent(contentId, product) {
      const $content = $(`#${contentId}`);

      if (product.type === 'complex') {
         loadComplexProductContent($content, product);
      } else if (product.type === 'curtains') {
         loadCurtainProductContent($content, product);
      } else {
         loadSimpleProductContent($content, product);
      }
   }

   function loadComplexProductContent($content, product) {
      const buttonText = `Add Item to ${product.name}`;

      const $wrapper = $(`
        <div class="product-details-wrapper">
           <div class="complex-product-layout">
              <div class="items-tabs-sidebar">
                 <div class="items-tabs-header">
                    <h6><i class="fa fa-list mr-2"></i>Items</h6>
                    <button type="button" class="btn btn-sm btn-primary add-product-item-btn" data-product="${product.id}">
                       <i class="fa fa-plus mr-1"></i> ${buttonText}
                    </button>
                 </div>
                 <div class="items-tabs-container">
                    <div class="empty-items-tabs">
                       <i class="fa fa-cube"></i>
                       <p>No items added yet</p>
                    </div>
                 </div>
              </div>
              <div class="item-details-content">
                 <div class="product-details-header">
                    <div class="product-header-with-image">
                       <div class="header-image-preview">
                          <i class="fa ${product.icon}"></i>
                       </div>
                       <h6><i class="fa fa-info-circle mr-2"></i>${product.name} Details</h6>
                    </div>
                    <div class="compact-header-details">
                       <div class="compact-header-group">
                          <label>Width (m)</label>
                          <input type="number" class="form-control dimension-width" placeholder="0.00" step="0.01" min="0">
                       </div>
                       <div class="compact-header-group">
                          <label>Length/Height (m)</label>
                          <input type="number" class="form-control dimension-length" placeholder="0.00" step="0.01" min="0">
                       </div>
                       <div class="compact-header-group">
                          <label>Unit Price ($)</label>
                          <input type="number" class="form-control unit-price" placeholder="0.00" step="0.01" min="0">
                       </div>
                       <div class="compact-header-group">
                          <label>Total Price ($)</label>
                          <input type="number" class="form-control total-price" placeholder="0.00" step="0.01" min="0" readonly>
                       </div>
                    </div>
                 </div>
                 <div class="product-details-body">
                    <div class="empty-item-selection">
                       <i class="fa fa-hand-pointer"></i>
                       <p>Select an item to view and edit details</p>
                    </div>
                 </div>
              </div>
           </div>
        </div>
     `);

      $content.html($wrapper);

      setupDimensionCalculations(product.id);
      setupPriceCalculations(product.id);
   }

   // UPDATED: Material section with image on left, details on right and pillow subcategory tabs
   function loadSimpleProductContent($content, product) {
      const $wrapper = $(`
        <div class="simple-product-content">
           <div class="compact-product-details">
              <div class="compact-section-header">
                 <h6><i class="fa fa-cube mr-2"></i>${product.name} Details</h6>
              </div>
              <div class="compact-details-with-image">
                 <div class="compact-image-preview">
                    <i class="fa fa-image"></i>
                 </div>
                 <div class="compact-details-fields">
                    <div class="compact-detail-group">
                       <label>Width (m)</label>
                       <input type="number" class="form-control dimension-width" placeholder="0.00" step="0.01" min="0">
                    </div>
                    <div class="compact-detail-group">
                       <label>Length (m)</label>
                       <input type="number" class="form-control dimension-length" placeholder="0.00" step="0.01" min="0">
                    </div>
                    <div class="compact-detail-group">
                       <label>Height (m)</label>
                       <input type="number" class="form-control dimension-height" placeholder="0.00" step="0.01" min="0">
                    </div>
                    <div class="compact-detail-group">
                       <label>Unit Price ($)</label>
                       <input type="number" class="form-control unit-price" placeholder="0.00" step="0.01" min="0">
                    </div>
                    <div class="compact-detail-group">
                       <label>Total Price ($)</label>
                       <input type="number" class="form-control total-price" placeholder="0.00" step="0.01" min="0" readonly>
                    </div>
                 </div>
              </div>
           </div>
           
           <!-- UPDATED Material Section with Image on Left and Details on Right -->
           <div class="material-section">
              <h6><i class="fa fa-layer-group mr-2"></i>Material Selection</h6>
              <div class="material-tabs" id="materialTabs-${product.id}">
                 ${materialCategories.map(category => `
                    <button class="material-tab ${category.id === 'metal' ? 'active' : ''}" data-category="${category.id}">
                       ${category.name}
                    </button>
                 `).join('')}
              </div>
              <div class="material-tabs-content" id="materialTabsContent-${product.id}">
                 ${materialCategories.map((category, index) => `
                    <div class="material-tab-content ${index === 0 ? 'active' : ''}" id="materialContent-${product.id}-${category.id}">
                       ${category.id === 'pillow' ? 
                          // Pillow subcategories with horizontal tabs
                          `<div class="pillow-subcategories-section">
                              <h6>Pillow Components</h6>
                              <div class="pillow-subcategories-tabs" id="pillowTabs-${product.id}">
                                 ${category.subcategories.map((subcat, subIndex) => `
                                    <button class="pillow-subcategory-tab ${subIndex === 0 ? 'active' : ''}" 
                                            data-subcategory="${subcat.id}" data-product="${product.id}">
                                       ${subcat.name}
                                    </button>
                                 `).join('')}
                              </div>
                              ${category.subcategories.map((subcat, subIndex) => `
                                 <div class="pillow-subcategory-content ${subIndex === 0 ? 'active' : ''}" 
                                      id="pillowContent-${product.id}-${subcat.id}">
                                    <div class="pillow-subcategory-details">
                                       <div class="material-inputs-compact">
                                          <div class="material-compact-image">
                                             <i class="fa fa-image"></i>
                                          </div>
                                          <div class="material-compact-fields">
                                             <div class="material-input">
                                                <label>Material Grade</label>
                                                <select class="form-control material-grade">
                                                   <option value="">Select Grade</option>
                                                   <option value="standard">Standard</option>
                                                   <option value="premium">Premium</option>
                                                   <option value="economy">Economy</option>
                                                </select>
                                             </div>
                                             <div class="material-input">
                                                <label>Material Type</label>
                                                <select class="form-control material-type-select">
                                                   <option value="">Select Material</option>
                                                   ${category.defaultMaterials.map(material => `
                                                      <option value="${material.id}">${material.name}</option>
                                                   `).join('')}
                                                </select>
                                             </div>
                                             <div class="material-input">
                                                <label>Area/Weight</label>
                                                <input type="text" class="form-control area-weight" placeholder="Enter area or weight">
                                             </div>
                                          </div>
                                       </div>
                                       <div class="file-input-wrapper" style="margin-top: 12px;">
                                          <button type="button" class="btn btn-sm btn-outline-primary">
                                             <i class="fa fa-upload mr-1"></i> Upload ${subcat.name} Image
                                          </button>
                                          <input type="file" class="pillow-image-input" data-subcategory="${subcat.id}">
                                       </div>
                                    </div>
                                 </div>
                              `).join('')}
                           </div>` : 
                          // Standard material layout for non-pillow categories (Image on left, details on right)
                          `<div class="material-inputs-compact">
                              <div class="material-compact-image">
                                 <i class="fa fa-image"></i>
                              </div>
                              <div class="material-compact-fields">
                                 <div class="material-input">
                                    <label>Material Grade</label>
                                    <select class="form-control material-grade">
                                       <option value="">Select Grade</option>
                                       <option value="standard">Standard</option>
                                       <option value="premium">Premium</option>
                                       <option value="economy">Economy</option>
                                    </select>
                                 </div>
                                 <div class="material-input">
                                    <label>Material Type</label>
                                    <select class="form-control material-type-select">
                                       <option value="">Select Material</option>
                                       ${category.defaultMaterials.map(material => `
                                          <option value="${material.id}">${material.name}</option>
                                       `).join('')}
                                    </select>
                                 </div>
                                 <div class="material-input">
                                    <label>Area/Weight</label>
                                    <input type="text" class="form-control area-weight" placeholder="Enter area or weight">
                                 </div>
                              </div>
                           </div>
                           <div class="file-input-wrapper" style="margin-top: 12px;">
                              <button type="button" class="btn btn-sm btn-outline-primary">
                                 <i class="fa fa-upload mr-1"></i> Upload Material Image
                              </button>
                              <input type="file" class="material-image-input" data-file-type="image">
                           </div>`
                       }
                    </div>
                 `).join('')}
              </div>
           </div>
        </div>
     `);

      $content.html($wrapper);

      setupDimensionCalculations(product.id);
      setupPriceCalculations(product.id);
      setupMaterialTabs(product.id);
      setupPillowSubcategoryTabs(product.id);
   }

   // ADDED: Curtain product content with accessory section working like items section
   function loadCurtainProductContent($content, product) {
      const $wrapper = $(`
        <div class="simple-product-content">
           <div class="compact-product-details">
              <div class="compact-section-header">
                 <h6><i class="fa fa-cube mr-2"></i>${product.name} Details</h6>
              </div>
              <div class="compact-details-with-image">
                 <div class="compact-image-preview">
                    <i class="fa fa-image"></i>
                 </div>
                 <div class="compact-details-fields">
                    <div class="compact-detail-group">
                       <label>Width (m)</label>
                       <input type="number" class="form-control dimension-width" placeholder="0.00" step="0.01" min="0">
                    </div>
                    <div class="compact-detail-group">
                       <label>Length (m)</label>
                       <input type="number" class="form-control dimension-length" placeholder="0.00" step="0.01" min="0">
                    </div>
                    <div class="compact-detail-group">
                       <label>Height (m)</label>
                       <input type="number" class="form-control dimension-height" placeholder="0.00" step="0.01" min="0">
                    </div>
                    <div class="compact-detail-group">
                       <label>Unit Price ($)</label>
                       <input type="number" class="form-control unit-price" placeholder="0.00" step="0.01" min="0">
                    </div>
                    <div class="compact-detail-group">
                       <label>Total Price ($)</label>
                       <input type="number" class="form-control total-price" placeholder="0.00" step="0.01" min="0" readonly>
                    </div>
                 </div>
              </div>
           </div>
           
           <!-- UPDATED Material Section with Image on Left and Details on Right -->
           <div class="material-section">
              <h6><i class="fa fa-layer-group mr-2"></i>Material Selection</h6>
              <div class="material-tabs" id="materialTabs-${product.id}">
                 ${materialCategories.map(category => `
                    <button class="material-tab ${category.id === 'metal' ? 'active' : ''}" data-category="${category.id}">
                       ${category.name}
                    </button>
                 `).join('')}
              </div>
              <div class="material-tabs-content" id="materialTabsContent-${product.id}">
                 ${materialCategories.map((category, index) => `
                    <div class="material-tab-content ${index === 0 ? 'active' : ''}" id="materialContent-${product.id}-${category.id}">
                       ${category.id === 'pillow' ? 
                          // Pillow subcategories with horizontal tabs
                          `<div class="pillow-subcategories-section">
                              <h6>Pillow Components</h6>
                              <div class="pillow-subcategories-tabs" id="pillowTabs-${product.id}">
                                 ${category.subcategories.map((subcat, subIndex) => `
                                    <button class="pillow-subcategory-tab ${subIndex === 0 ? 'active' : ''}" 
                                            data-subcategory="${subcat.id}" data-product="${product.id}">
                                       ${subcat.name}
                                    </button>
                                 `).join('')}
                              </div>
                              ${category.subcategories.map((subcat, subIndex) => `
                                 <div class="pillow-subcategory-content ${subIndex === 0 ? 'active' : ''}" 
                                      id="pillowContent-${product.id}-${subcat.id}">
                                    <div class="pillow-subcategory-details">
                                       <div class="material-inputs-compact">
                                          <div class="material-compact-image">
                                             <i class="fa fa-image"></i>
                                          </div>
                                          <div class="material-compact-fields">
                                             <div class="material-input">
                                                <label>Material Grade</label>
                                                <select class="form-control material-grade">
                                                   <option value="">Select Grade</option>
                                                   <option value="standard">Standard</option>
                                                   <option value="premium">Premium</option>
                                                   <option value="economy">Economy</option>
                                                </select>
                                             </div>
                                             <div class="material-input">
                                                <label>Material Type</label>
                                                <select class="form-control material-type-select">
                                                   <option value="">Select Material</option>
                                                   ${category.defaultMaterials.map(material => `
                                                      <option value="${material.id}">${material.name}</option>
                                                   `).join('')}
                                                </select>
                                             </div>
                                             <div class="material-input">
                                                <label>Area/Weight</label>
                                                <input type="text" class="form-control area-weight" placeholder="Enter area or weight">
                                             </div>
                                          </div>
                                       </div>
                                       <div class="file-input-wrapper" style="margin-top: 12px;">
                                          <button type="button" class="btn btn-sm btn-outline-primary">
                                             <i class="fa fa-upload mr-1"></i> Upload ${subcat.name} Image
                                          </button>
                                          <input type="file" class="pillow-image-input" data-subcategory="${subcat.id}">
                                       </div>
                                    </div>
                                 </div>
                              `).join('')}
                           </div>` : 
                          // Standard material layout for non-pillow categories (Image on left, details on right)
                          `<div class="material-inputs-compact">
                              <div class="material-compact-image">
                                 <i class="fa fa-image"></i>
                              </div>
                              <div class="material-compact-fields">
                                 <div class="material-input">
                                    <label>Material Grade</label>
                                    <select class="form-control material-grade">
                                       <option value="">Select Grade</option>
                                       <option value="standard">Standard</option>
                                       <option value="premium">Premium</option>
                                       <option value="economy">Economy</option>
                                    </select>
                                 </div>
                                 <div class="material-input">
                                    <label>Material Type</label>
                                    <select class="form-control material-type-select">
                                       <option value="">Select Material</option>
                                       ${category.defaultMaterials.map(material => `
                                          <option value="${material.id}">${material.name}</option>
                                       `).join('')}
                                    </select>
                                 </div>
                                 <div class="material-input">
                                    <label>Area/Weight</label>
                                    <input type="text" class="form-control area-weight" placeholder="Enter area or weight">
                                 </div>
                              </div>
                           </div>
                           <div class="file-input-wrapper" style="margin-top: 12px;">
                              <button type="button" class="btn btn-sm btn-outline-primary">
                                 <i class="fa fa-upload mr-1"></i> Upload Material Image
                              </button>
                              <input type="file" class="material-image-input" data-file-type="image">
                           </div>`
                       }
                    </div>
                 `).join('')}
              </div>
           </div>
           
           <!-- UPDATED: Curtain Options Section with Accessory Layout like Items Section -->
           <div class="curtain-options-section">
              <h6><i class="fa fa-cog mr-2"></i>Curtain Options</h6>
              <div class="curtain-controls">
                 <div class="curtain-control">
                    <label>Opening Direction</label>
                    <select class="form-control opening-direction">
                       <option value="">Select Direction</option>
                       <option value="left">Left Opening</option>
                       <option value="right">Right Opening</option>
                       <option value="center">Center Opening</option>
                       <option value="top">Top Opening</option>
                    </select>
                 </div>
                 <div class="curtain-control">
                    <label>Open With</label>
                    <select class="form-control open-with">
                       <option value="">Select Option</option>
                       <option value="cord">Cord</option>
                       <option value="wand">Wand</option>
                       <option value="motorized">Motorized</option>
                       <option value="manual">Manual</option>
                    </select>
                 </div>
              </div>
              
              <h6 style="margin-top: 16px;"><i class="fa fa-plus-circle mr-2"></i>Accessories</h6>
              <div class="accessory-layout">
                 <div class="accessory-tabs-sidebar">
                    <div class="accessory-tabs-header">
                       <h6><i class="fa fa-list mr-2"></i>Accessories</h6>
                       <button type="button" class="btn btn-sm btn-primary add-accessory-btn" data-product="${product.id}">
                          <i class="fa fa-plus mr-1"></i> Add Accessory
                       </button>
                    </div>
                    <div class="accessory-tabs-container">
                       <div class="empty-accessory-tabs">
                          <i class="fa fa-puzzle-piece"></i>
                          <p>No accessories added yet</p>
                       </div>
                    </div>
                 </div>
                 <div class="accessory-details-content">
                    <div class="accessory-details-header">
                       <div class="product-header-with-image">
                          <div class="header-image-preview">
                             <i class="fa fa-puzzle-piece"></i>
                          </div>
                          <h6><i class="fa fa-info-circle mr-2"></i>Accessory Details</h6>
                       </div>
                    </div>
                    <div class="accessory-details-body">
                       <div class="empty-accessory-selection">
                          <i class="fa fa-hand-pointer"></i>
                          <p>Select an accessory to view and edit details</p>
                       </div>
                    </div>
                 </div>
              </div>
           </div>
        </div>
     `);

      $content.html($wrapper);

      setupDimensionCalculations(product.id);
      setupPriceCalculations(product.id);
      setupMaterialTabs(product.id);
      setupPillowSubcategoryTabs(product.id);
   }

   function setupDimensionCalculations(productId) {
      $(`#product-${productId}-room${state.currentRoom} .dimension-width, #product-${productId}-room${state.currentRoom} .dimension-length`).on('input', function() {
         const width = parseFloat($(`#product-${productId}-room${state.currentRoom} .dimension-width`).val()) || 0;
         const length = parseFloat($(`#product-${productId}-room${state.currentRoom} .dimension-length`).val()) || 0;
         const area = width * length;
         const unitPrice = parseFloat($(`#product-${productId}-room${state.currentRoom} .unit-price`).val()) || 0;
         const totalPrice = unitPrice * area;
         $(`#product-${productId}-room${state.currentRoom} .total-price`).val(totalPrice.toFixed(2));
      });
   }

   function setupPriceCalculations(productId) {
      $(`#product-${productId}-room${state.currentRoom} .unit-price`).on('input', function() {
         const unitPrice = parseFloat($(this).val()) || 0;
         const width = parseFloat($(`#product-${productId}-room${state.currentRoom} .dimension-width`).val()) || 0;
         const length = parseFloat($(`#product-${productId}-room${state.currentRoom} .dimension-length`).val()) || 0;
         const area = width * length;
         const totalPrice = unitPrice * area;
         $(`#product-${productId}-room${state.currentRoom} .total-price`).val(totalPrice.toFixed(2));
      });
   }

   // Setup material tabs functionality
   function setupMaterialTabs(itemId) {
      $(`#materialTabs-${itemId} .material-tab`).on('click', function(e) {
         e.preventDefault();
         const categoryId = $(this).data('category');

         $(`#materialTabs-${itemId} .material-tab`).removeClass('active');
         $(this).addClass('active');

         $(`#materialTabsContent-${itemId} .material-tab-content`).removeClass('active');
         $(`#materialContent-${itemId}-${categoryId}`).addClass('active');
      });
   }

   // Setup pillow subcategory tabs functionality
   function setupPillowSubcategoryTabs(productId) {
      $(`#pillowTabs-${productId} .pillow-subcategory-tab`).on('click', function(e) {
         e.preventDefault();
         const subcategoryId = $(this).data('subcategory');
         const productId = $(this).data('product');

         $(`#pillowTabs-${productId} .pillow-subcategory-tab`).removeClass('active');
         $(this).addClass('active');

         $(`#materialContent-${productId}-pillow .pillow-subcategory-content`).removeClass('active');
         $(`#pillowContent-${productId}-${subcategoryId}`).addClass('active');
      });
   }

   // UPDATED: Add item to product with improved material section layout - FIXED ISSUE
   function addItemToProduct(roomId, productId, item) {
      console.log('Adding item to product:', item.name, 'room:', roomId, 'product:', productId);

      const $productContent = $(`#product-${productId}-room${roomId}`);
      const $tabsContainer = $productContent.find('.items-tabs-container');
      const $emptyState = $tabsContainer.find('.empty-items-tabs');
      const $detailsBody = $productContent.find('.product-details-body');
      const $emptyDetails = $detailsBody.find('.empty-item-selection');

      if ($emptyState.length) {
         $emptyState.remove();
      }

      const existingTab = $tabsContainer.find(`[data-item-id="${item.id}"]`);
      if (existingTab.length) {
         alert('This item has already been added.');
         return;
      }

      const tabId = `item-${item.id}-${productId}-room${roomId}`;
      const $tab = $(`
        <div class="items-tab" data-item-id="${item.id}" id="${tabId}-tab">
           <span class="items-tab-name">${item.name}</span>
           <div class="items-tab-close" title="Remove item">
              <i class="fa fa-times"></i>
           </div>
        </div>
     `);

      $tabsContainer.append($tab);

      // UPDATED: Item details with improved material section layout - FIXED ISSUE
      const $detailsContent = $(`
        <div class="item-details" id="${tabId}" style="display: none;">
           <div class="item-details-subheader">
              <h6><i class="fa fa-info-circle mr-2"></i>Item Details</h6>
           </div>
           <div class="product-details-body">
              <div class="enhanced-category-item">
                 <div class="enhanced-item-header">
                    <div class="enhanced-item-name">${item.name}</div>
                 </div>
                 <div class="enhanced-details-with-image">
                    <div class="enhanced-image-preview">
                       <i class="fa fa-image"></i>
                    </div>
                    <div class="enhanced-details-fields">
                       <div class="detail-group">
                          <label>Quantity</label>
                          <input type="number" class="form-control item-qty" placeholder="0" min="1" value="1">
                       </div>
                       <div class="detail-group">
                          <label>Unit Price</label>
                          <input type="number" class="form-control item-price" placeholder="0.00" min="0" step="0.01" value="0.00">
                       </div>
                       <div class="detail-group">
                          <label>Length (m)</label>
                          <input type="number" class="form-control item-length item-dims" placeholder="0.00" step="0.01" min="0">
                       </div>
                       <div class="detail-group">
                          <label>Width (m)</label>
                          <input type="number" class="form-control item-width item-dims" placeholder="0.00" step="0.01" min="0">
                       </div>
                       <div class="detail-group">
                          <label>Height (m)</label>
                          <input type="number" class="form-control item-height item-dims" placeholder="0.00" step="0.01" min="0">
                       </div>
                       <div class="detail-group">
                          <label>Material</label>
                          <input type="text" class="form-control item-material" placeholder="Material type">
                       </div>
                    </div>
                 </div>
                 <div class="detail-group" style="margin-top: 12px;">
                    <label>Notes</label>
                    <textarea class="form-control item-notes" placeholder="Additional notes..." rows="2"></textarea>
                 </div>
              </div>
              
              <!-- UPDATED Material Tabs Section with Improved Layout -->
              <div class="material-section">
                 <h6><i class="fa fa-layer-group mr-2"></i>Material Selection</h6>
                 <div class="material-tabs" id="materialTabs-${item.id}">
                    ${materialCategories.map(category => `
                       <button class="material-tab ${category.id === 'metal' ? 'active' : ''}" data-category="${category.id}">
                          ${category.name}
                       </button>
                    `).join('')}
                 </div>
                 <div class="material-tabs-content" id="materialTabsContent-${item.id}">
                    ${materialCategories.map((category, index) => `
                       <div class="material-tab-content ${index === 0 ? 'active' : ''}" id="materialContent-${item.id}-${category.id}">
                       </div>
                    `).join('')}
                 </div>
              </div>
           </div>
        </div>
     `);

      if ($emptyDetails.length) {
         $emptyDetails.remove();
      }

      $detailsBody.append($detailsContent);

      // Load material content for each category
      materialCategories.forEach((category, index) => {
         const $categoryContent = $(`#materialContent-${item.id}-${category.id}`);
         
         if (category.id === 'pillow') {
            $categoryContent.html(`
               <div class="pillow-subcategories-section">
                  <h6>Pillow Components</h6>
                  <div class="pillow-subcategories-tabs" id="pillowTabs-${item.id}">
                     ${category.subcategories.map((subcat, subIndex) => `
                        <button class="pillow-subcategory-tab ${subIndex === 0 ? 'active' : ''}" 
                                data-subcategory="${subcat.id}" data-item="${item.id}">
                           ${subcat.name}
                        </button>
                     `).join('')}
                  </div>
                  ${category.subcategories.map((subcat, subIndex) => `
                     <div class="pillow-subcategory-content ${subIndex === 0 ? 'active' : ''}" 
                          id="pillowContent-${item.id}-${subcat.id}">
                        <div class="pillow-subcategory-details">
                           <div class="material-inputs-compact">
                              <div class="material-compact-image">
                                 <i class="fa fa-image"></i>
                              </div>
                              <div class="material-compact-fields">
                                 <div class="material-input">
                                    <label>Material Grade</label>
                                    <select class="form-control material-grade">
                                       <option value="">Select Grade</option>
                                       <option value="standard">Standard</option>
                                       <option value="premium">Premium</option>
                                       <option value="economy">Economy</option>
                                    </select>
                                 </div>
                                 <div class="material-input">
                                    <label>Material Type</label>
                                    <select class="form-control material-type-select">
                                       <option value="">Select Material</option>
                                       ${category.defaultMaterials.map(material => `
                                          <option value="${material.id}">${material.name}</option>
                                       `).join('')}
                                    </select>
                                 </div>
                                 <div class="material-input">
                                    <label>Area/Weight</label>
                                    <input type="text" class="form-control area-weight" placeholder="Enter area or weight">
                                 </div>
                              </div>
                           </div>
                           <div class="file-input-wrapper" style="margin-top: 12px;">
                              <button type="button" class="btn btn-sm btn-outline-primary">
                                 <i class="fa fa-upload mr-1"></i> Upload ${subcat.name} Image
                              </button>
                              <input type="file" class="pillow-image-input" data-subcategory="${subcat.id}">
                           </div>
                        </div>
                     </div>
                  `).join('')}
               </div>
            `);
         } else {
            $categoryContent.html(`
               <div class="material-inputs-compact">
                  <div class="material-compact-image">
                     <i class="fa fa-image"></i>
                  </div>
                  <div class="material-compact-fields">
                     <div class="material-input">
                        <label>Material Grade</label>
                        <select class="form-control material-grade">
                           <option value="">Select Grade</option>
                           <option value="standard">Standard</option>
                           <option value="premium">Premium</option>
                           <option value="economy">Economy</option>
                        </select>
                     </div>
                     <div class="material-input">
                        <label>Material Type</label>
                        <select class="form-control material-type-select">
                           <option value="">Select Material</option>
                           ${category.defaultMaterials.map(material => `
                              <option value="${material.id}">${material.name}</option>
                           `).join('')}
                        </select>
                     </div>
                     <div class="material-input">
                        <label>Area/Weight</label>
                        <input type="text" class="form-control area-weight" placeholder="Enter area or weight">
                     </div>
                  </div>
               </div>
               <div class="file-input-wrapper" style="margin-top: 12px;">
                  <button type="button" class="btn btn-sm btn-outline-primary">
                     <i class="fa fa-upload mr-1"></i> Upload Material Image
                  </button>
                  <input type="file" class="material-image-input" data-file-type="image">
               </div>
            `);
         }
      });

      activateItemTab($tab, roomId, productId);

      setupMaterialTabs(item.id);
      setupPillowSubcategoryTabsForItem(item.id);

      $tab.find('.items-tab-close').on('click', function(e) {
         e.stopPropagation();
         removeItemFromProduct($tab, roomId, productId, item.id);
      });

      updateRoomStatus(`room${roomId}`);
   }

   // ADDED: Add accessory to curtain product
   function addAccessoryToProduct(roomId, productId, accessory) {
      console.log('Adding accessory to product:', accessory.name, 'room:', roomId, 'product:', productId);

      const $productContent = $(`#product-${productId}-room${roomId}`);
      const $tabsContainer = $productContent.find('.accessory-tabs-container');
      const $emptyState = $tabsContainer.find('.empty-accessory-tabs');
      const $detailsBody = $productContent.find('.accessory-details-body');
      const $emptyDetails = $detailsBody.find('.empty-accessory-selection');

      if ($emptyState.length) {
         $emptyState.remove();
      }

      const existingTab = $tabsContainer.find(`[data-accessory-id="${accessory.id}"]`);
      if (existingTab.length) {
         alert('This accessory has already been added.');
         return;
      }

      const tabId = `accessory-${accessory.id}-${productId}-room${roomId}`;
      const $tab = $(`
        <div class="accessory-tab" data-accessory-id="${accessory.id}" id="${tabId}-tab">
           <span class="accessory-tab-name">${accessory.name}</span>
           <div class="accessory-tab-close" title="Remove accessory">
              <i class="fa fa-times"></i>
           </div>
        </div>
     `);

      $tabsContainer.append($tab);

      // Accessory details content
      const $detailsContent = $(`
        <div class="accessory-details" id="${tabId}" style="display: none;">
           <div class="enhanced-category-item">
              <div class="enhanced-item-header">
                 <div class="enhanced-item-name">${accessory.name}</div>
              </div>
              <div class="enhanced-details-with-image">
                 <div class="enhanced-image-preview">
                    <i class="fa fa-image"></i>
                 </div>
                 <div class="enhanced-details-fields">
                    <div class="detail-group">
                       <label>Quantity</label>
                       <input type="number" class="form-control accessory-qty" placeholder="0" min="1" value="1">
                    </div>
                    <div class="detail-group">
                       <label>Unit Price</label>
                       <input type="number" class="form-control accessory-price" placeholder="0.00" min="0" step="0.01" value="0.00">
                    </div>
                    <div class="detail-group">
                       <label>Accessory Type</label>
                       <select class="form-control accessory-type-select">
                          <option value="">Select ${accessory.name} Type</option>
                          ${accessory.options.map(option => `
                             <option value="${option.id}">${option.name}</option>
                          `).join('')}
                       </select>
                    </div>
                 </div>
              </div>
              <div class="accessory-option" id="${accessory.id}-preview" style="display: none; margin-top: 12px;">
                 <div class="accessory-option-image">
                    <i class="fa fa-image"></i>
                 </div>
                 <div class="accessory-option-details">
                    <div class="accessory-option-name"></div>
                    <div class="accessory-option-description"></div>
                 </div>
              </div>
           </div>
        </div>
     `);

      if ($emptyDetails.length) {
         $emptyDetails.remove();
      }

      $detailsBody.append($detailsContent);

      activateAccessoryTab($tab, roomId, productId);

      // Setup accessory type selection preview
      $(`#${tabId} .accessory-type-select`).on('change', function() {
         const selectedOptionId = $(this).val();
         const previewId = `${accessory.id}-preview`;
         
         if (selectedOptionId) {
            const selectedOption = accessory.options.find(opt => opt.id === selectedOptionId);
            if (selectedOption) {
               $(`#${tabId} #${previewId} .accessory-option-name`).text(selectedOption.name);
               $(`#${tabId} #${previewId} .accessory-option-description`).text(selectedOption.description);
               $(`#${tabId} #${previewId}`).show();
            }
         } else {
            $(`#${tabId} #${previewId}`).hide();
         }
      });

      $tab.find('.accessory-tab-close').on('click', function(e) {
         e.stopPropagation();
         removeAccessoryFromProduct($tab, roomId, productId, accessory.id);
      });
   }

   // Setup pillow subcategory tabs for items
   function setupPillowSubcategoryTabsForItem(itemId) {
      $(`#pillowTabs-${itemId} .pillow-subcategory-tab`).on('click', function(e) {
         e.preventDefault();
         const subcategoryId = $(this).data('subcategory');
         const itemId = $(this).data('item');

         $(`#pillowTabs-${itemId} .pillow-subcategory-tab`).removeClass('active');
         $(this).addClass('active');

         $(`#materialContent-${itemId}-pillow .pillow-subcategory-content`).removeClass('active');
         $(`#pillowContent-${itemId}-${subcategoryId}`).addClass('active');
      });
   }

   function activateItemTab($tab, roomId, productId) {
      const itemId = $tab.data('item-id');
      const $productContent = $(`#product-${productId}-room${roomId}`);

      $productContent.find('.items-tab').removeClass('active');
      $productContent.find('.item-details').hide();

      $tab.addClass('active');
      $(`#item-${itemId}-${productId}-room${roomId}`).show();
   }

   // ADDED: Activate accessory tab
   function activateAccessoryTab($tab, roomId, productId) {
      const accessoryId = $tab.data('accessory-id');
      const $productContent = $(`#product-${productId}-room${roomId}`);

      $productContent.find('.accessory-tab').removeClass('active');
      $productContent.find('.accessory-details').hide();

      $tab.addClass('active');
      $(`#accessory-${accessoryId}-${productId}-room${roomId}`).show();
   }

   function removeItemFromProduct($tab, roomId, productId, itemId) {
      console.log('Removing item from product');

      $tab.remove();
      $(`#item-${itemId}-${productId}-room${roomId}`).remove();

      const $productContent = $(`#product-${productId}-room${roomId}`);
      const $tabsContainer = $productContent.find('.items-tabs-container');
      const $detailsBody = $productContent.find('.product-details-body');
      const $tabs = $tabsContainer.find('.items-tab');

      if ($tabs.length === 0) {
         $tabsContainer.html(`
            <div class="empty-items-tabs">
               <i class="fa fa-cube"></i>
               <p>No items added yet</p>
            </div>
         `);

         $detailsBody.html(`
            <div class="product-details-content">
               <div class="empty-item-selection">
                  <i class="fa fa-hand-pointer"></i>
                  <p>Select an item to view and edit details</p>
               </div>
            </div>
         `);
      } else {
         const $firstTab = $tabs.first();
         activateItemTab($firstTab, roomId, productId);
      }

      updateRoomStatus(`room${roomId}`);
   }

   // ADDED: Remove accessory from product
   function removeAccessoryFromProduct($tab, roomId, productId, accessoryId) {
      console.log('Removing accessory from product');

      $tab.remove();
      $(`#accessory-${accessoryId}-${productId}-room${roomId}`).remove();

      const $productContent = $(`#product-${productId}-room${roomId}`);
      const $tabsContainer = $productContent.find('.accessory-tabs-container');
      const $detailsBody = $productContent.find('.accessory-details-body');
      const $tabs = $tabsContainer.find('.accessory-tab');

      if ($tabs.length === 0) {
         $tabsContainer.html(`
            <div class="empty-accessory-tabs">
               <i class="fa fa-puzzle-piece"></i>
               <p>No accessories added yet</p>
            </div>
         `);

         $detailsBody.html(`
            <div class="empty-accessory-selection">
               <i class="fa fa-hand-pointer"></i>
               <p>Select an accessory to view and edit details</p>
            </div>
         `);
      } else {
         const $firstTab = $tabs.first();
         activateAccessoryTab($firstTab, roomId, productId);
      }
   }

   function updateRoomStatus(roomId) {
      const $roomPane = $(`#${roomId}`);
      const $statusIndicator = $(`#${roomId}-tab .status-indicator`);

      let hasItems = false;
      let allComplete = true;

      $roomPane.find('.enhanced-category-item').each(function() {
         hasItems = true;
         const $qty = $(this).find('.item-qty');
         const $itemLength = $(this).find('.item-length');
         const $itemWidth = $(this).find('.item-width');
         const $itemHeight = $(this).find('.item-height');
         const $name = $(this).find('.enhanced-item-name');

         if (!$qty.val() || !$itemLength.val() || !$itemWidth.val() || !$itemHeight.val() || !$name.val()) {
            allComplete = false;
            return false;
         }
      });

      $statusIndicator.removeClass('status-empty status-incomplete status-complete');

      if (!hasItems) {
         $statusIndicator.addClass('status-empty');
      } else if (allComplete) {
         $statusIndicator.addClass('status-complete');
      } else {
         $statusIndicator.addClass('status-incomplete');
      }
   }

   // Event handlers
   $('#addRoomBtn').on('click', function() {
      const roomNumber = getNextRoomNumber();
      const roomId = 'room' + roomNumber;

      const $tabLi = $(`
        <li class="nav-item">
           <a class="nav-link room-tab" id="${roomId}-tab" data-toggle="tab" href="#${roomId}" role="tab" aria-controls="${roomId}" data-room="${roomNumber}">
              <div class="room-header">
                 <span class="status-indicator status-empty"></span>
                 <span class="room-title">Room ${roomNumber}</span>
                 <span class="close-room ml-2" title="Remove room">
                    <i class="fa fa-times"></i>
                 </span>
              </div>
           </a>
        </li>
     `);
      $('#roomTabs .nav-item:has(.add-room-btn)').before($tabLi);

      const $pane = $(`
        <div class="tab-pane fade" id="${roomId}" role="tabpanel" aria-labelledby="${roomId}-tab" data-room="${roomNumber}">
           <div class="product-tabs-wrapper">
              <div class="product-tabs-header">
                 <div class="room-info-form">
                    <div class="form-group-small">
                       <label for="floorName-${roomId}">Floor Name</label>
                       <input type="text" class="form-control-small" id="floorName-${roomId}" placeholder="Enter floor name">
                    </div>
                    <div class="form-group-small">
                       <label for="roomName-${roomId}">Room Name</label>
                       <input type="text" class="form-control-small" id="roomName-${roomId}" placeholder="Enter room name">
                    </div>
                    <div class="form-group-small">
                       <label>Room Image</label>
                       <div class="image-upload-container">
                          <div class="image-preview" id="imagePreview-${roomId}">
                             <i class="fa fa-image"></i>
                          </div>
                          <div class="file-input-wrapper">
                             <button type="button" class="btn btn-sm btn-outline-primary">
                                <i class="fa fa-upload mr-1"></i> Upload
                             </button>
                             <input type="file" class="room-image-input" id="roomImage-${roomId}" data-file-type="image" data-room="${roomNumber}">
                          </div>
                       </div>
                    </div>
                 </div>
                 <button type="button" class="btn btn-sm add-item-to-room-btn" data-room="${roomNumber}">
                    <i class="fa fa-plus mr-1"></i> Add Item To Room ${roomNumber}
                 </button>
              </div>
              <div class="product-tabs-container" id="productTabs-room${roomNumber}">
                 <div class="product-empty-state">
                    <i class="fa fa-cube"></i>
                    <p>No products added yet</p>
                 </div>
              </div>
              <div class="product-content-area" id="productContent-room${roomNumber}">
                 <div class="product-empty-state">
                    <i class="fa fa-hand-pointer"></i>
                    <p>Select a product to configure details</p>
                 </div>
              </div>
           </div>
        </div>
     `);

      $('#roomTabsContent').append($pane);
      $(`#${roomId}-tab`).tab('show');
      updateRoomStatus(roomId);
      addRoomToState(roomNumber);
   });

   $(document).on('click', '.add-item-to-room-btn', function(e) {
      e.preventDefault();
      e.stopPropagation();

      let roomId = $(this).data('room');
      console.log('Add item to room button clicked, roomId from data:', roomId);

      if (!roomId) {
         const $roomPane = $(this).closest('.tab-pane');
         if ($roomPane.length) {
            roomId = $roomPane.data('room');
            console.log('RoomId from pane data:', roomId);
         }
      }

      if (roomId) {
         console.log('Final roomId for qualification modal:', roomId);
         showQualificationModal(roomId);
      } else {
         console.error('Could not determine roomId for product');
      }
   });

   $(document).on('click', '.qualification-option', function() {
      console.log('Qualification option clicked:', $(this).data('qualification'));
      $('.qualification-option').removeClass('selected');
      $(this).addClass('selected');
      state.selectedQualification = $(this).data('qualification');
      $('#confirmAddQualification').prop('disabled', false);
   });

   $('#confirmAddQualification').on('click', function() {
      console.log('Confirm add qualification clicked');
      console.log('Current state:', {
         selectedQualification: state.selectedQualification,
         currentRoom: state.currentRoom
      });

      if (state.selectedQualification && state.currentRoom) {
         const qualification = productOptions.find(q => q.id === state.selectedQualification);
         if (qualification) {
            const roomId = state.currentRoom;

            hideQualificationModal();

            setTimeout(() => {
               console.log('Showing multi-select modal with roomId:', roomId);
               showMultiSelectModal(qualification, roomId);
            }, 100);
         }
      }
   });

   $(document).on('click', '.multi-select-option', function() {
      const productId = $(this).data('product-id');
      console.log('Multi-select option clicked:', productId);

      $(this).toggleClass('selected');

      if ($(this).hasClass('selected')) {
         if (!state.selectedProducts.includes(productId)) {
            state.selectedProducts.push(productId);
         }
      } else {
         state.selectedProducts = state.selectedProducts.filter(id => id !== productId);
      }

      $('#confirmMultiSelect').prop('disabled', state.selectedProducts.length === 0);
   });

   $('#confirmMultiSelect').on('click', function() {
      console.log('Confirm multi-select clicked');

      const qualification = $('#multiSelectModal').data('qualification');
      const roomId = $('#multiSelectModal').data('roomId');

      console.log('Data for multi-select addition:', {
         qualification: qualification,
         roomId: roomId,
         selectedProducts: state.selectedProducts
      });

      if (state.selectedProducts.length > 0 && roomId && qualification) {
         let products = [];

         if (qualification.id === 'fitout') {
            products = fitoutProducts.filter(product => state.selectedProducts.includes(product.id));
         } else if (qualification.id === 'curtains') {
            products = curtainProducts.filter(product => state.selectedProducts.includes(product.id));
         } else {
            products = [qualification];
         }

         console.log('Adding products:', products);
         products.forEach(product => {
            addProductTab(roomId, product);
         });
         hideMultiSelectModal();
      } else {
         console.error('Missing data for multi-select addition');
         alert('Please select at least one product to continue.');
      }
   });

   $(document).on('click', '.item-option', function() {
      console.log('Item option clicked:', $(this).data('item-id'));
      $('.item-option').removeClass('selected');
      $(this).addClass('selected');

      const itemId = $(this).data('item-id');
      const productData = itemData[state.currentProductType] || itemData.electrical;

      state.selectedItem = null;
      Object.keys(productData.categories).forEach(catKey => {
         const category = productData.categories[catKey];
         const item = category.items.find(i => i.id === itemId);
         if (item) {
            state.selectedItem = item;
         }
      });

      $('#confirmSelectItem').prop('disabled', false);
   });

   $('#confirmSelectItem').on('click', function() {
      console.log('Confirm item selection clicked');

      if (state.selectedItem && state.currentProductType) {
         const $activeProductTab = $('.product-tab.active');
         if ($activeProductTab.length === 0) {
            console.error('No active product tab found');
            return;
         }

         const productId = $activeProductTab.data('product');
         const roomId = $activeProductTab.closest('.product-tabs-container').attr('id').replace('productTabs-room', '');

         console.log('Adding item to:', {
            productId,
            roomId,
            item: state.selectedItem
         });

         addItemToProduct(roomId, productId, state.selectedItem);
         hideItemSelectionModal();
      } else {
         console.error('Missing item for selection');
      }
   });

   // ADDED: Accessory selection handlers
   $(document).on('click', '.item-option', function() {
      if ($(this).closest('#accessoryOptions').length) {
         console.log('Accessory option clicked:', $(this).data('accessory-id'));
         $('.item-option').removeClass('selected');
         $(this).addClass('selected');

         const accessoryId = $(this).data('accessory-id');
         state.selectedAccessory = curtainAccessories.find(acc => acc.id === accessoryId);

         $('#confirmSelectAccessory').prop('disabled', false);
      }
   });

   $('#confirmSelectAccessory').on('click', function() {
      console.log('Confirm accessory selection clicked');

      if (state.selectedAccessory && state.currentProductId) {
         const $activeProductTab = $('.product-tab.active');
         if ($activeProductTab.length === 0) {
            console.error('No active product tab found');
            return;
         }

         const productId = $activeProductTab.data('product');
         const roomId = $activeProductTab.closest('.product-tabs-container').attr('id').replace('productTabs-room', '');

         console.log('Adding accessory to:', {
            productId,
            roomId,
            accessory: state.selectedAccessory
         });

         addAccessoryToProduct(roomId, productId, state.selectedAccessory);
         hideAccessorySelectionModal();
      } else {
         console.error('Missing accessory for selection');
      }
   });

   $(document).on('click', '.item-category-tab', function() {
      const categoryKey = $(this).data('category');
      console.log('Item category tab clicked:', categoryKey);
      $('.item-category-tab').removeClass('active');
      $(this).addClass('active');

      const productData = itemData[state.currentProductType] || itemData.electrical;
      loadItemCategory(categoryKey, productData.categories[categoryKey]);
   });

   $(document).on('click', '.add-product-item-btn', function() {
      const productId = $(this).data('product');
      console.log('Add product item button clicked:', productId);
      showItemSelectionModal(productId);
   });

   // ADDED: Add accessory button handler
   $(document).on('click', '.add-accessory-btn', function() {
      const productId = $(this).data('product');
      console.log('Add accessory button clicked:', productId);
      showAccessorySelectionModal(productId);
   });

   $(document).on('click', '.close-room', function(e) {
      e.stopPropagation();
      const $tab = $(this).closest('a.room-tab');
      const totalRooms = $('#roomTabs a.room-tab').length;

      if (totalRooms <= 1) {
         alert('At least one room must be present.');
         return;
      }

      const roomId = $tab.attr('href').replace('#', '');
      const isActive = $tab.hasClass('active');

      $tab.closest('.nav-item').remove();
      $(`#${roomId}`).remove();

      renumberRooms();

      if (isActive) {
         const $remainingTabs = $('#roomTabs a.room-tab');
         if ($remainingTabs.length > 0) {
            const $firstTab = $remainingTabs.first();
            $firstTab.tab('show');
         }
      }
   });

   $(document).on('click', '.product-tab', function(e) {
      if (!$(e.target).closest('.product-tab-close').length) {
         activateProductTab($(this));
      }
   });

   $(document).on('click', '.product-tab-close', function(e) {
      e.stopPropagation();
      const $tab = $(this).closest('.product-tab');
      const $tabsContainer = $tab.closest('.product-tabs-container');

      if ($tabsContainer.find('.product-tab').length <= 1) {
         alert('At least one product must remain in the room.');
         return;
      }

      const productId = $tab.data('product');
      const roomId = $tabsContainer.attr('id').replace('productTabs-room', '');

      $(`#product-${productId}-room${roomId}`).remove();
      $tab.remove();

      if ($tab.hasClass('active')) {
         const $firstTab = $tabsContainer.find('.product-tab').first();
         if ($firstTab.length) {
            activateProductTab($firstTab);
         }
      }
   });

   $(document).on('click', '.items-tab', function() {
      const $tab = $(this);
      const $productContent = $tab.closest('.product-content');
      const productId = $productContent.attr('id').replace('product-', '').replace(/-room\d+$/, '');
      const roomId = $productContent.attr('id').match(/room(\d+)/)[1];

      if (!$tab.find('.items-tab-close').is(':hover')) {
         activateItemTab($tab, roomId, productId);
      }
   });

   // ADDED: Accessory tab click handler
   $(document).on('click', '.accessory-tab', function() {
      const $tab = $(this);
      const $productContent = $tab.closest('.product-content');
      const productId = $productContent.attr('id').replace('product-', '').replace(/-room\d+$/, '');
      const roomId = $productContent.attr('id').match(/room(\d+)/)[1];

      if (!$tab.find('.accessory-tab-close').is(':hover')) {
         activateAccessoryTab($tab, roomId, productId);
      }
   });

   $('#closeQualificationModal').on('click', hideQualificationModal);
   $('#closeMultiSelectModal').on('click', hideMultiSelectModal);
   $('#closeItemSelectionModal').on('click', hideItemSelectionModal);
   $('#closeAccessorySelectionModal').on('click', hideAccessorySelectionModal);

   $('#qualificationModal').on('click', function(e) {
      if (e.target === this) hideQualificationModal();
   });
   $('#multiSelectModal').on('click', function(e) {
      if (e.target === this) hideMultiSelectModal();
   });
   $('#itemSelectionModal').on('click', function(e) {
      if (e.target === this) hideItemSelectionModal();
   });
   $('#accessorySelectionModal').on('click', function(e) {
      if (e.target === this) hideAccessorySelectionModal();
   });

   // Initialize
   initializeQualificationModal();
   setupQualificationSearch();
   setupProductSearch();
   setupImageUpload();
   addRoomToState(1);
   updateRoomStatus('room1');

   console.log('System initialized successfully with fixed item details issue and accessory section like items section');
});
</script>
