<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class TransactionService
{
    /**
     * Executes database operations within a transaction.
     *
     * @param callable $databaseOperations The closure containing database interactions.
     * @param callable|null $onCommit Optional closure to run after successful commit.
     * @param callable|null $onError Optional closure to run on exception before rollback.
     * @return mixed The result of the $databaseOperations closure if successful, false otherwise.
     * @throws Throwable Re-throws the original exception after rollback.
     */
    public function run(callable $databaseOperations, ?callable $onCommit = null, ?callable $onError = null): mixed
    {
        DB::beginTransaction();

        try {
            // Execute the main database operations
            $result = $databaseOperations();

            // Commit the transaction
            DB::commit();
            Log::info('Database transaction committed successfully.');

            // Execute post-commit actions if provided
            if (is_callable($onCommit)) {
                try {
                    $onCommit($result); // Pass result to commit handler
                } catch (Throwable $commitError) {
                    // Log error in commit handler, but transaction is already committed.
                    Log::error('Error executing onCommit actions after transaction commit.', [
                        'error' => $commitError->getMessage(),
                        'trace' => $commitError->getTraceAsString(),
                    ]);
                    // Decide if this error should be propagated or just logged.
                    // Re-throwing might be confusing as the DB commit was successful.
                }
            }

            return $result; // Return the result from the database operations

        } catch (Throwable $e) {
            // Execute error handler before rollback if provided
            if (is_callable($onError)) {
                try {
                    $onError($e);
                } catch (Throwable $onErrorError) {
                    Log::critical('Error executing onError handler during transaction rollback preparation.', [
                        'original_error' => $e->getMessage(),
                        'onError_error' => $onErrorError->getMessage(),
                    ]);
                }
            }

            // Rollback the transaction
            DB::rollback();

            Log::error('Database transaction rolled back.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Re-throw the original exception so the caller knows about the failure
            throw $e;
        }
    }
} 