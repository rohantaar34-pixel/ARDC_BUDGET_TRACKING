<?php

// database/migrations/xxxx_xx_xx_create_documents_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentsTable extends Migration
{
    public function up()
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('document_number')->unique(); // Document ID/Number
            $table->string('title'); // Document title/name
            $table->text('description')->nullable(); // Document details/description
            $table->string('document_type'); // Type: contract, invoice, report, etc.
            $table->string('category')->nullable(); // Category: financial, legal, technical, etc.
            
            // File paths
            $table->string('file_path')->nullable(); // Main document file path
            $table->string('scanned_image_path')->nullable(); // Scanned image path
            $table->string('thumbnail_path')->nullable(); // Thumbnail for preview
            
            // Document metadata
            $table->string('original_filename')->nullable();
            $table->string('file_size')->nullable(); // File size in KB/MB
            $table->string('file_extension')->nullable();
            $table->string('mime_type')->nullable();
            
            // Additional details
            $table->date('document_date')->nullable(); // Date on the document
            $table->date('expiry_date')->nullable(); // If document expires
            $table->string('status')->default('active'); // active, archived, expired
            $table->integer('version')->default(1); // Document version
            
            // Relationships
            $table->foreignId('project_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Tracking
            $table->integer('view_count')->default(0);
            $table->integer('download_count')->default(0);
            $table->timestamp('date_added')->useCurrent();
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['document_number', 'status']);
            $table->index(['project_id', 'document_type']);
            $table->index('date_added');
        });
    }

    public function down()
    {
        Schema::dropIfExists('documents');
    }
}