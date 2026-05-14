<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('beneficiaries', function (Blueprint $table) {
            $table->id();
            $table->string('beneficiary_number')->unique();
            $table->uuid('qr_token')->unique();
            $table->string('full_name');
            $table->string('address', 500);
            $table->string('barangay');
            $table->date('birthdate');
            $table->string('gender', 20);
            $table->string('contact_number', 30)->nullable();
            $table->string('civil_status', 30);
            $table->string('valid_id_path')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('category', 60);
            $table->string('status', 20)->default('pending')->index();
            $table->text('notes')->nullable();
            $table->date('date_issued')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beneficiaries');
    }
};
