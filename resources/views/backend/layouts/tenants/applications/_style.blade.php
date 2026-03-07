    <style>
        .icon-service {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            font-size: 24px;
        }

        .stats-card {
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .nav-tabs .nav-link {
            color: #6c757d;
            border: none;
            border-bottom: 2px solid transparent;
            padding: 12px 24px;
        }

        .nav-tabs .nav-link.active {
            color: #fff !important;
            background: #D9A600;
        }

        .nav-tabs .nav-link:hover {
            color: #fff !important;
            background: #fcd554;
        }

        .badge {
            padding: 6px 12px;
            font-weight: 500;
        }

        /* Modal Styling */
        #reservationModal .modal-content {
            border-radius: 10px;
            overflow: scroll;
        }

        #reservationModal .modal-header {
            background: linear-gradient(135deg, #D9A600 0%, #fcd554 100%);
            border: none;
        }

        #reservationModal .modal-body {
            max-height: 70vh;
            overflow-y: auto;
        }

        .reservation-details h6 {
            color: #333;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .reservation-details .card {
            border: 1px solid #e9ecef;
            border-radius: 8px;
        }

        .reservation-details .card-body {
            background-color: #f8f9fa;
        }

        .reservation-details small.text-muted {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .reservation-details strong {
            font-size: 0.95rem;
            color: #495057;
        }

        /* Scrollbar Styling */
        #reservationModal .modal-body::-webkit-scrollbar {
            width: 8px;
        }

        #reservationModal .modal-body::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        #reservationModal .modal-body::-webkit-scrollbar-thumb {
            background: #D9A600;
        }

        #reservationModal .modal-body::-webkit-scrollbar-thumb:hover {
            background: #b88d00;
        }

        /* Reservation Modal Scroll Fix */
        #reservationModal .modal-dialog {
            max-height: 90vh;
        }

        #reservationModal .modal-content {
            max-height: 90vh;
            display: flex;
            flex-direction: column;
        }

        #reservationModal .modal-body {
            overflow-y: auto;
            max-height: calc(90vh - 140px);
            /* header + footer height */
        }

        .reservation-items-wrapper {
            max-height: 300px;
            overflow-y: auto;
        }

        /* Single Email Modal Styling */
        #singleEmailModal .modal-content {
            border-radius: 10px;
            overflow: scroll;
        }

        #singleEmailModal .modal-header {
            background: linear-gradient(135deg, #17a2b8 0%, #6dd5ed 100%);
            border: none;
        }

        #singleEmailModal .modal-body {
            max-height: 70vh;
            overflow-y: auto;
        }

        .application-details h6 {
            color: #333;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .application-details small.text-muted {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .application-details strong {
            font-size: 0.95rem;
            color: #495057;
        }

        /* Scrollbar Styling for Single Email Modal */
        #singleEmailModal .modal-body::-webkit-scrollbar {
            width: 8px;
        }

        #singleEmailModal .modal-body::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        #singleEmailModal .modal-body::-webkit-scrollbar-thumb {
            background: #17a2b8;
        }

        #singleEmailModal .modal-body::-webkit-scrollbar-thumb:hover {
            background: #138496;
        }

        /* Single Email Modal Scroll Fix */
        #singleEmailModal .modal-dialog {
            max-height: 90vh;
        }

        #singleEmailModal .modal-content {
            max-height: 90vh;
            display: flex;
            flex-direction: column;
        }

        #singleEmailModal .modal-body {
            overflow-y: auto;
            max-height: calc(90vh - 140px);
        }
    </style>